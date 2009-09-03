<?php
import('org.rhaco.storage.db.Dao');

class OpenpearPackage extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'package';
    
    protected $id;
    protected $name;
    protected $description;
    protected $url;
    protected $public_level;
    protected $external_repository;
    protected $external_repository_type;
    protected $favored_count;
    protected $recent_changeset;
    protected $released_at;
    protected $latest_release_id;
    protected $author_id;
    protected $license;
    protected $license_uri;
    protected $notify;
    protected $created;
    protected $updated;
    
    static protected $__id__ = 'type=serial';
    static protected $__name__ = 'type=string,unique=true,require=true';
    static protected $__description__ = 'type=string,max=250';
    static protected $__url__ = 'type=string';
    static protected $__public_level__ = 'type=number';
    static protected $__external_repository__ = 'type=string';
    static protected $__external_repository_type__ = 'type=choice(subversion,git)';
    static protected $__favored_count__ = 'type=number,default=0';
    static protected $__recent_changeset__ = 'type=number';
    static protected $__released_at__ = 'type=timestamp';
    static protected $__latest_release_id__ = 'type=number';
    static protected $__author_id__ = 'type=number';
    static protected $__license__ = 'type=string';
    static protected $__license_uri__ = 'type=string';
    static protected $__notify__ = 'type=string';
    static protected $__created__ = 'type=timestamp';
    static protected $__updated__ = 'type=timestamp';
    
    protected $author;
    protected $releases;
    protected $maintainers;
    protected $favored_maintainers;
    protected $latest_release;
    protected $package_tags;
    protected $primary_tag;
    static protected $__author__ = 'type=OpenpearMaintainer,extra=true';
    static protected $__releases__ = 'type=OpenpearRelease[],extra=true';
    static protected $__maintainers__ = 'type=OpenpearMaintainer[],extra=true';
    static protected $__favored_maintainers__ = 'type=OpenpearMaintainer[],extra=true';
    static protected $__latest_release__ = 'type=OpenpearRelease,extra=true';
    static protected $__package_tags__ = 'type=OpenpearPackageTag[],extra=true';
    static protected $__primary_tag__ = 'type=OpenpearTag,extra=true';
    
    // TODO: ちゃんとしたメッセージを英語で書きたい(出力時国際化)
    const NOTIFY_WANTED = 'メンテナ募集してます';
    const NOTIFY_DEPRECATED = 'メンテナンスされていません';
    
    public function __init__(){
        $this->created = time();
        $this->updated = time();
        $this->public_level = 1;
        $this->license = 'New BSD License (BSD)';
        $this->favored_count = 0;
    }
    
    protected function __create_verify__(){
    }
    protected function __after_create__(){
        $queue = new OpenpearNewprojectQueue();
        $queue->package_id($this->id());
        $queue->maintainer_id($this->author_id());
        $queue->save();
    }
    
    public function is_public(){
        return (bool) ($this->public_level > 0);
    }
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
    public function liked(OpenpearMaintainer $maintainer){
        try {
            C(OpenpearFavorite)->find_get(Q::eq('package_id', $this->id()), Q::eq('maintainer_id', $maintainer->id()));
            return true;
        } catch(Exception $e){}
        return false;
    }
    public function add_maintainer(OpenpearMaintainer $maintainer, $role='lead'){
        try {
            $charge = new OpenpearCharge();
            $charge->maintainer_id($maintainer->id());
            $charge->package_id($this->id());
            $charge->role($role);
            $charge->save();
        } catch(Exception $e){
            Exceptions::add($e);
        }
    }
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
    public function remove_tag($tag_id){
        try {
            $tag = C(OpenpearTag)->find_get(Q::eq('id', $tag_id));
            $package_tag = C(OpenpearPackageTag)->find_get(Q::eq('tag_id', $tag->id()), Q::eq('package_id', $this->id()));
            $package_tag->find_delete(Q::eq('tag_id', $tag->id()), Q::eq('package_id', $this->id()));
            $package_tag->after_delete();
            C($package_tag)->commit();
        } catch(Exception $e){}
    }
    
    public function installName(){
        if($this->latest_release()->version_stab() === 'stable'){
            return $this->name();
        }
        return sprintf('%s-%s', $this->name(), $this->latest_release()->version_stab());
    }
    protected function getAuthor(){
        if($this->author instanceof OpenpearMaintainer){
            return $this->author;
        }
        try {
            $this->author = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->author_id()));
        } catch(Exception $e){}
        return $this->author;
    }
    protected function getReleases(){
        if(!empty($this->releases)) return $this->releases;
        try{
            $this->releases = C(OpenpearRelease)->find_all(Q::eq('package_id', $this->id()));
        } catch(Exception $e){}
        return $this->releases;
    }
    protected function getMaintainers(){
        if(!empty($this->maintainers)) return $this->maintainers;
        try{
            $charges = C(OpenpearCharge)->find_all(Q::eq('package_id', $this->id()));
            $maintainers = array();
            foreach($charges as $charge){
                $maintainers[] = $charge->maintainer();
            }
            $this->maintainers = $maintainers;
            return $this->maintainers;
        } catch(Exception $e){}
        return array();
    }
    protected function getFavored_maintainers(){
        if(!empty($this->favored_maintainers)) return $this->favored_maintainers;
        try{
            $favs = C(OpenpearFavorite)->find_all(Q::eq('package_id', $this->id()));
            $favored_maintainers = array();
            foreach($favs as $fav){
                $favored_maintainers[] = $fav->maintainer();
            }
            $this->favored_maintainers = $favored_maintainers;
            return $this->favored_maintainers;
        } catch(Exception $e){}
        return array();
    }
    protected function getLatest_release(){
        if($this->latest_release instanceof OpenpearRelease) return $this->latest_release;
        try{
            $this->latest_release = C(OpenpearRelease)->find_get(Q::eq('package_id', $this->id()), Q::order('-id'));
            return $this->latest_release;
        } catch(Exception $e){}
        $release = new OpenpearRelease();
        $release->package_id($this->id());
        return $release;
    }
    protected function getPackage_tags(){
        if(!empty($this->package_tags)) return $this->package_tags();
        try {
            $this->package_tags = C(OpenpearPackageTag)->find_all(Q::eq('package_id', $this->id()), Q::order('-prime'));
        } catch(Exception $e){}
        return $this->package_tags;
    }
    protected function getPrimary_tag(){
        if($this->primary_tag instanceof OpenpearTag) return $this->primary_tag;
        try {
            foreach($this->package_tags() as $package_tag){
                if($package_tag->prime() === true){
                    $this->primary_tag = $package_tag->tag();
                    break;
                }
            }
        } catch(Exception $e){}
        return $this->primary_tag;
    }
    
    protected function verifyName(){
        return (bool) preg_match('@^[A-Za-z][A-Za-z0-9_]+$@', $this->name);
    }
}
