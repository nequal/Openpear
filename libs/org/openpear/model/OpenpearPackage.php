<?php
import('org.rhaco.storage.db.Dao');

/**
 * Package
 *
 * @var serial $id
 * @var string $name @{"unique":true,"require":true}
 * @var string $description @{"require":true,"max":"250"}
 * @var string $url
 * @var integer $public_level
 * @var string $external_repository
 * @var choice $external_repository_type @{"choices":["Git","Mercurial","Subversion"]}
 * @var integer $download_count @{"default":"0"}
 * @var integer $favored_count @{"default":"0"}
 * @var integer $recent_changeset
 * @var timestamp $released_at
 * @var integer $latest_release_id
 * @var integer $author_id
 * @var string $license
 * @var string $license_uri
 * @var string $notify
 * @var choice $package_type @{"choices":["pear","pecl"]}
 * @var timestamp $created
 * @var timestamp $updated
 * @var integer $repository_uri_select @{"extra":true}
 */
class OpenpearPackage extends Dao
{
    protected $id;
    protected $name;
    protected $description;
    protected $url;
    protected $public_level;
    protected $external_repository;
    protected $external_repository_type;
    protected $download_count = 0;
    protected $favored_count = 0;
    protected $recent_changeset;
    protected $released_at;
    protected $latest_release_id;
    protected $author_id;
    protected $license;
    protected $license_uri;
    protected $notify;
    protected $package_type = 'pear';
    protected $created;
    protected $updated;
    
    protected $repository_uri_select = 1;
    
    private $author;
    private $releases = array();
    private $maintainers = array();
    private $favored_maintainers = array();
    private $latest_release;
    private $package_tags = array();
    private $primary_tag;
    private $liked = array();
    private $recent_changeset_object;

    static private $cached_packages = array();
    
    const NOTIFY_RECRUITE = 'This package is accepting maintainers for admission.';
    const NOTIFY_NOMAINT = 'This package is not maintained.';
    
    // TODO: Charge に移動
    public function maintainer_role(OpenpearMaintainer $maintainer) {
        $cache_key = self::cache_key('maintainer_role');
        if (Store::has($cache_key)) {
            $role = Store::get($cache_key);
        } else {
            $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $this->id), Q::eq('maintainer_id', $maintainer->id()));
            $role = $charge->role();
            Store::set($cache_key, $role);
        }
        return $role;
    }

    public function generate_release_notes() {
        if ($this->is_latest_release_id()) {
            $latest_release = $this->latest_release();
            $changesets = C(OpenpearChangeset)->find_all(Q::eq('package_id', $this->id), Q::gte('created', $latest_release->created()));
        } else {
            $changesets = C(OpenpearChangeset)->find_all(Q::eq('package_id', $this->id));
        }
        $notes = '';
        foreach ($changesets as $changeset) {
            $notes .= '- ';
            $notes .= $changeset->comment();
            $notes .= "\n";
        }
        return $notes;
    }

    /**
     * リポジトリ種類別のコマンドを取得
     * @return string $command
     **/
    public function repoistory_type_cmd() {
        switch ($this->external_repository_type()) {
            case "Git":
                $s = "git clone";
                break;
            case "Mercurial":
                $s = "hg clone";
                break;
            case "Subversion":
                $s = "svn co";
                break;
            default:
                $s = "";
        }
        return $s;
    }

    /**
     * 活発なカテゴリを取得する
     * @param int $limit
     * @return array OpenpearTag[]
     **/
    static public function getActiveCategories($limit=10){
        $tag_ids_count = C(OpenpearPackageTag)->find_count_by('package_id', 'tag_id', Q::eq('prime', true));
        arsort($tag_ids_count);
        if (count($tag_ids_count) == 0) {
            $categories = array();
        } else {
            $categories = C(OpenpearTag)->find_all(Q::in('id', array_slice(array_keys($tag_ids_count), 0, $limit)));
        }
        return $categories;
    }
    
    /**
     * パッケージ情報を取得する
     * @param int $id
     * @param bool $cache
     * @return OpenpearPackage
     **/
    static public function get_package($id, $cache=true) {
        $cache_key = self::cache_key($id);
        if ($cache) {
            Log::debug('cache on');
            if (isset(self::$cached_packages[$id])) {
                return self::$cached_packages[$id]; 
            } else if (Store::has($cache_key)) {
                $package = Store::get($cache_key);
                self::$cached_packages[$id] = $package;
                return $package;
            }
        }
        $package = C(__CLASS__)->find_get(Q::eq('id', $id));
        Store::set($cache_key, $package, OpenpearConfig::object_cache_timeout(3600));
        return $package;
    }

    static private function cache_key($id) {
        return array(__CLASS__, $id);
    }

    /**
     * 誰でも参加可能か
     * @return bool
     **/
    public function is_public(){
        return (bool) ($this->public_level > 0);
    }

    /**
     * Github ですか？
     * @return bool
     **/
    public function is_github() {
        return (bool) isset($this->external_repository) && strpos($this->external_repository, "github.com");
    }
    public function github_url() {
        if($this->is_github() && preg_match('/github\.com[\/:](.+?)\/(.+?)\.git/', $this->external_repository, $match)) {
            return sprintf('https://github.com/%s/%s', $match[1], $match[2]);
        }
        return '';
    }

    /**
     * メンテナに権限があるか
     * @param OpenpearMaintainer $maintainer
     * @param bool $exception 例外を出力するかどうか
     * @return bool
     **/
    public function permission(OpenpearMaintainer $maintainer, $exception=true){
        try {
            $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $this->id()), Q::eq('maintainer_id', $maintainer->id()));
        } catch(Exception $e){
            if($exception === true){
                throw new OpenpearException('Permission denied');
            }
            return false;
        }
        return $charge;
    }

    /**
     * メンテナのお気に入りかどうか
     * @param OpenpearMaintainer $maintainer
     * @return bool
     **/
    public function liked(OpenpearMaintainer $maintainer){
        if (isset($this->liked[$maintainer->id()])) {
            return $this->liked[$maintainer->id()];
        }
        try {
            C(OpenpearFavorite)->find_get(Q::eq('package_id', $this->id()), Q::eq('maintainer_id', $maintainer->id()));
            $this->liked[$maintainer->id()] = true;
        } catch(Exception $e){
            $this->liked[$maintainer->id()] = false;
        }
        return $this->liked[$maintainer->id()];
    }

    /**
     * メンテナを追加する
     * @param OpenpearMaintainer $maintainer
     * @param string $role
     * @return void
     **/
    public function add_maintainer(OpenpearMaintainer $maintainer, $role='lead'){
        $charge = new OpenpearCharge();
        $charge->maintainer_id($maintainer->id());
        $charge->package_id($this->id());
        $charge->role($role);
        $charge->save();
    }

    /**
     * カテゴリを追加する
     * @param string $tag_name
     * @param bool $prime
     * @return void
     **/
    public function add_tag($tag_name, $prime=false){
        try {
            $tag = C(OpenpearTag)->find_get(Q::eq('name', $tag_name));
        } catch(Exception $e){
            $tag = new OpenpearTag();
            $tag->name($tag_name);
            $tag->prime($prime);
            $tag->save();
        }
        $package_tag = new OpenpearPackageTag();
        $package_tag->package_id($this->id());
        $package_tag->tag_id($tag->id());
        $package_tag->prime($prime);
        $package_tag->save();
    }

    /**
     * カテゴリを削除する
     * @param int $tag_id
     * @return void
     **/
    public function remove_tag($tag_id){
        try {
            $tag = C(OpenpearTag)->find_get(Q::eq('id', $tag_id));
            $package_tag = C(OpenpearPackageTag)->find_get(Q::eq('tag_id', $tag->id()), Q::eq('package_id', $this->id()));
            C($package_tag)->find_delete(Q::eq('tag_id', $tag->id()), Q::eq('package_id', $this->id()));
            $package_tag->after_delete();
            C($package_tag)->commit();
        } catch(Exception $e){
            Log::debug($e);
        }
    }

    /**
     * notify フラグ
     * @return array
     **/
    public function getflags() {
        return explode(',', $this->notify);
    }

    /**
     * notify フラグをセットする
     * @param string $flag
     * @return void
     **/
    public function setflag($flag) {
        $notifies = array_map('trim', explode(',', $this->notify));
        if (in_array($flag, array('recruite', 'nomaint')) && array_search($flag, $notifies) === false) {
            $notifies[] = $flag;
        }
        $this->notify = implode(',', $notifies);
    }

    /**
     * notify フラグを削除する
     * @param string $flag
     * @return void
     **/
    public function rmflag($flag) {
        $notifies = array_map('trim', explode(',', $this->notify));
        if (in_array($flag, array('recruite', 'nomaint')) && ($key = array_search($flag, $notifies)) !== false) {
            unset($notifies[$key]);
        }
        $this->notify = implode(',', $notifies);
    }

    /**
     * PEAR コマンドでインストール時に使う名前を取得
     * @return string $package_name
     **/
    public function installName(){
        if($this->latest_release()->version_stab() === 'stable'){
            return $this->name();
        }
        return sprintf('%s-%s', $this->name(), $this->latest_release()->version_stab());
    }
    
    public function author(){
        // setting author
        if($this->author instanceof OpenpearMaintainer === false){
            try {
                $this->author = OpenpearMaintainer::get_maintainer($this->author_id());
            } catch(Exception $e){
                return new OpenpearMaintainer;
            }
        }
        return $this->author;
    }

    public function releases(){
        // setting releases[]
        if(empty($this->releases)){
            try{
                $this->releases = C(OpenpearRelease)->find_all(Q::eq('package_id', $this->id()));
            } catch(Exception $e){}
        }
        return $this->releases;
    }

    public function maintainers(){
        // setting maintainers[]
        if(empty($this->maintainers)){
            try{
                $charges = C(OpenpearCharge)->find_all(Q::eq('package_id', $this->id()));
                $maintainers = array();
                foreach($charges as $charge){
                    $maintainers[] = $charge->maintainer();
                }
                $this->maintainers = $maintainers;
            } catch(Exception $e){}
        }
        return $this->maintainers;
    }

    public function favored_maintainers(){
        // setting favored maintainers
        if(empty($this->favored_maintainers)){
            try{
                $favs = C(OpenpearFavorite)->find_all(Q::eq('package_id', $this->id()));
                $favored_maintainers = array();
                foreach($favs as $fav){
                    $favored_maintainers[] = $fav->maintainer();
                }
                $this->favored_maintainers = $favored_maintainers;
            } catch(Exception $e){}
        }
        return $this->favored_maintainers;
    }

    public function latest_release(){
        // setting latest release
        if($this->latest_release instanceof OpenpearRelease === false){
            try{
                $this->latest_release = C(OpenpearRelease)->find_get(Q::eq('package_id', $this->id()), Q::order('-id'));
            } catch(Exception $e){
                $release = new OpenpearRelease();
                $release->package_id($this->id());
                $this->latest_release = $release;
            }
        }
        return $this->latest_release;
    }

    public function package_tags(){
        // setting package tags
        if(empty($this->package_tags)){
            try {
                $this->package_tags = C(OpenpearPackageTag)->find_all(Q::eq('package_id', $this->id()), Q::order('-prime'));
            } catch(Exception $e){}
        }
        return $this->package_tags;
    }

    public function primary_tag(){
        // setting primary tag
        if($this->primary_tag instanceof OpenpearTag === false){
            try {
                foreach($this->package_tags() as $package_tag){
                    if($package_tag->prime() === true){
                        $this->primary_tag = $package_tag->tag();
                        break;
                    }
                }
            } catch(Exception $e){}
        }
        return $this->primary_tag;
    }
    
    public function recent_changeset_object() {
        if($this->recent_changeset_object instanceof OpenpearChangeset === false && $this->is_recent_changeset()){
            try {
                $this->recent_changeset_object = OpenpearChangeset::get_changeset($this->recent_changeset);
            } catch(Exception $e){}
        }
        return $this->recent_changeset_object;
    }

    protected function __init__(){
        $this->created = time();
        $this->updated = time();
        $this->public_level = 1;
        $this->external_repository_type = "Git";
        $this->license = 'New BSD License (BSD)';
        $this->favored_count = 0;
    }

    protected function __create_verify__($commit){
    }

    protected function __after_create__($commit){
        if (!$this->is_external_repository()) {
            $queue = new OpenpearNewprojectQueue();
            $queue->package_id($this->id());
            $queue->maintainer_id($this->author_id());
            $queue->save();
        }

        $created_message = new Template();
        $created_message->vars('package', $this);
        $message = new OpenpearPackageMessage();
        $message->package_id($this->id());
        $message->description($created_message->read('messages/created.txt'));
        $message->save();

        $timeline = new OpenpearTimeline();
        $timeline->subject(sprintf('<a href="%s">%s</a> <span class="hl">created</span> a new package: <a href="%s">%s</a>',
            url('maintainer/'. $this->author()->name()),
            $this->author()->name(),
            url('package/'. $this->name()),
            $this->name()
        ));
        $timeline->description(htmlspecialchars($this->description()));
        $timeline->type('package_setting');
        $timeline->package_id($this->id());
        $timeline->maintainer_id($this->author_id());
        $timeline->save();
    }

    protected function __before_save__($commit) {
        $this->updated = time();
    }

    protected function __after_save__($commit) {
        Store::delete(self::cache_key($this->id));
    }

    protected function __verify_name__(){
         if(!preg_match('@^[A-Za-z][A-Za-z0-9_]+$@', $this->name)){
             Exceptions::add(new OpenpearException('name is NOT valid. ### FIXME ###'), 'name');
         }
         return true;
    }

    protected function __verify_url__() {
        if ($this->repository_uri_select() == 2 && empty($this->url)) {
            Exceptions::add(new OpenpearException('URL is required when using external repository'), 'url');
        }
    }

    protected function __verify_external_repository__(){
        if($this->repository_uri_select() == 2){
            if(empty($this->external_repository)){
                Exceptions::add(new OpenpearException('External Repository is required'), 'external_repository');
            }
        }
    }
}
