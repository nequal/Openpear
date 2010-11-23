<?php
import('org.rhaco.storage.db.Dao');
import('org.openpear.model.OpenpearMessage');

class OpenpearMaintainer extends Dao
{
    protected $id;
    protected $name;
    protected $mail;
    protected $fullname;
    protected $profile;
    protected $url;
    protected $location;
    protected $password;
    protected $svn_password;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__name__ = 'type=string,unique=true,require=true';
    static protected $__mail__ = 'type=email,require=true';
    static protected $__fullname__ = 'type=string';
    static protected $__profile__ = 'type=text';
    static protected $__url__ = 'type=string';
    static protected $__location__ = 'type=string';
    static protected $__password__ = 'type=string';
    static protected $__svn_password__ = 'type=string';
    static protected $__created__ = 'type=timestamp';
    
    protected $new_password;
    protected $new_password_conf;
    static protected $__new_password__ = 'extra=true';
    static protected $__new_password_conf__ = 'extra=true';

    static private $cached_maintainers = array();
    
    protected function __init__(){
        $this->created = time();
    }
    
    /**
     * アバターを取得
     * @param int $size
     * @return string $gravater_url
     **/
    public function avatar($size=16){
        return sprintf('http://www.gravatar.com/avatar/%s?s=%d', md5($this->mail()), $size);
    }

    /**
     * 参加しているパッケージリストを取得
     * @return array OpenpearPackage[]
     **/
    public function packages(){
        return OpenpearCharge::packages($this);
    }
    public function last_activity(){
        try {
            return C(OpenpearTimeline)->find_get(Q::eq('maintainer_id', $this->id()), Q::order('-created'))->created();
        } catch(Exception $e) {
            return $this->created();
        }
    }

    /**
     * メンテナ情報を取得する
     * @param int $id
     * @param bool $cache
     * @return OpenpearMaintainar
     **/
    static public function get_maintainer($id, $cache=true) {
        $cache_key = self::cache_key($id);
        if ($cache) {
            Log::debug('cache on');
            if (isset(self::$cached_maintainers[$id])) {
                return self::$cached_maintainers[$id]; 
            } else if (Store::has($cache_key)) {
                $maintainer = Store::get($cache_key);
                self::$cached_maintainers[$id] = $maintainer;
                return $maintainer;
            }
        }
        $maintainer = C(__CLASS__)->find_get(Q::eq('id', $id));
        Store::set($cache_key, $maintainer, OpenpearConfig::object_cache_timeout(3600));
        return $maintainer;
    }
    
    /**
     * 正しいパスワードか認証する
     * 過去のパスワードはひどいので適宜修正
     */
    public function certify($password){
        if($this->is_password()){
            if($this->password() === sha1($password)) return true;
            return false;
        }
        $org_password = $this->svn_password();
        $salt = substr($org_password, 0, 2);
        if($org_password === crypt($password, $salt)){
            $this->password = sha1($password);
            $this->save(true);
            return true;
        }
        return false;
    }

    static private function cache_key($id) {
        return array(__CLASS__, $id);
    }

    /**
     * 文字列表現
     */
    protected function __str__(){
        return empty($this->fullname)? $this->name(): $this->fullname();
    }

    /**
     * 作成/更新前処理
     */
    protected function __before_save__(){
        if($this->new_password() && $this->new_password() === $this->new_password_conf()){
            $this->password = sha1($this->new_password());
            $this->svn_password = crypt($this->new_password());
        }
    }
	/**
	 * @see vendors/org/rhaco/storage/db/Dao/Dao#__after_save__()
	 * @const string $svn_passwd_file リポジトリにアクセスするパスワード
	 */
    protected function __after_save__(){
        $template = new Template();
        $template->vars('maintainers', C(OpenpearMaintainer)->find_all());
        File::write(OpenpearConfig::svn_passwd_file(work_path('openpear.passwd')), $template->read('files/passwd.txt'));
        Store::delete(self::cache_key($this->id));
    }
    
    /**
     * 新規作成時検証
     */
    protected function __create_verify__(){
        if(!$this->new_password()){
            Exceptions::add(new Exception('Subversion Password is required'), 'new_password');
        }
        if(!$this->new_password_conf() || $this->new_password() !== $this->new_password_conf()){
            Exceptions::add(new Exception('Incorrect Confirm Password'), 'new_password_conf');
        }
    }
    
    /**
     * 作成後処理
     * - Subversion アカウントの作成
     * - メールの送信
     */
    protected function __after_create__(){
        $registered_message = new Template();
        $registered_message->vars('maintainer', $this);
        
        $message = new OpenpearMessage();
        $message->maintainer_to_id($this->id());
        $message->subject('Welcome to Openpear!');
        $message->description($registered_message->read('messages/registered.txt'));
        $message->type('system');
        $message->save();
        
        $message = new OpenpearMessage();
        $message->maintainer_to_id($this->id());
        $message->subject('Please join Openpear Group');
        $message->description('Do you already join the Openpear Group? <a href="http://groups.google.com/group/openpear">Openpear Group</a>');
        $message->type('system_notice');
        $message->mail(false);
        $message->save();
    }
    
    protected function __verify_url__(){
        if(!empty($this->url) && !preg_match('/s?https?:\/\/[\-_\.!~*\'\(\)a-zA-Z0-9;\/\?:@&=\+$,%#]+/i', $this->url)){
            return false;
        }
        return true;
    }
}
