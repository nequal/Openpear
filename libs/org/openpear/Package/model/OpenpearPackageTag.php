<?php
import('org.rhaco.storage.db.Dao');
import('org.openpear.Package.model.OpenpearPackage');
import('org.openpear.Package.model.OpenpearTag');

class OpenpearPackageTag extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'package_tag';
    
    protected $package_id;
    protected $tag_id;
    protected $prime;
    
    static protected $__package_id__ = 'type=number,require=true,primary=true';
    static protected $__tag_id__ = 'type=number,require=true,primary=true';
    static protected $__prime__ = 'type=boolean';
    
    protected $package;
    protected $tag;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__tag__ = 'type=OpenpeatTag,extra=true';
    
    public function __init__(){
        $this->prime = false;
    }
    
    static public function getActiveCategories($limit=10){
        $categories = array();
        try {
            $packages = C(OpenpearPackage)->find_all(new Paginator($limit*5, 1), Q::order('-recent_changeset'));
            foreach($packages as $package){
                foreach($package->package_tags() as $package_tag){
                    if($package_tag->prime() === true && !isset($categories[$package_tag->tag()->name()])){
                        $categories[$package_tag->tag()->name()] = $package_tag->tag();
                        if(count($categories) >= $limit){
                            break 2;
                        }
                    }
                }
            }
        } catch(Exception $e){
            $categories = C(OpenpearTag)->find_all(new Paginator($limit, 1), Q::order('name'));
        }
        return $categories;
    }
    protected function __after_save__(){
        $this->_reset_primaries();
        $this->_recount_tags();
        if($this->prime() === true){
            try {
                if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('package_id', $this->package_id())) > 0){
                    foreach(C(OpenpearPackageTag)->find_all(Q::eq('package_id', $this->package_id())) as $package_tag){
                        if($package_tag->tag_id() !== $this->tag_id() && $package_tag->prime() === true){
                            $package_tag->prime(false);
                            $package_tag->save();
                        }
                    }
                }
            } catch(Exception $e){}
        }
    }
    public function after_delete(){
        $this->_reset_primaries();
        $this->_recount_tags();
        if(C(OpenpearPackageTag)->find_count(Q::neq('package_id', $this->package_id()), Q::eq('tag_id', $this->tag_id())) === 0){
            $tag = $this->tag();
            $tag->delete();
        }
    }
    private function _recount_tags(){
        try {
            $tag = $this->tag();
            $tag->package_count(C(OpenpearPackageTag)->find_count(Q::eq('tag_id', $this->tag_id())));
            $tag->save();
        } catch (Exception $e){}
    }
    private function _reset_primaries(){
        try {
            $tag = C(OpenpearTag)->find_get(Q::eq('id', $this->tag_id()));
            if($this->prime() === true && $tag->prime() === true){
                return ;
            } else if($this->prime() === true && $tag->prime() === false){
                $tag->prime(true);
                $tag->save();
                return ;
            }
            switch($tag->prime()){
                case true:
                    if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('tag_id', $this->tag_id())) === 0){
                        $tag->prime(false);
                        $tag->save();
                    }
                    break;
                case false:
                    if(C(OpenpearPackageTag)->find_count(Q::eq('prime', true), Q::eq('tag_id', $this->tag_id())) > 0){
                        $tag->prime(true);
                        $tag->save();
                    }
                    break;
            }
        } catch(Exception $e){}
    }
    
    protected function getPackage(){
        if($this->package instanceof OpenpearPackage){
            return $this->package;
        }
        $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
        return $this->package;
    }
    protected function getTag(){
        if($this->tag instanceof OpenpearTag){
            return $this->tag;
        }
        $this->tag = C(OpenpearTag)->find_get(Q::eq('id', $this->tag_id()));
        return $this->tag;
    }
}