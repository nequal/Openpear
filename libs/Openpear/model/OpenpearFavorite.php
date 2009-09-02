<?php
import('org.rhaco.storage.db.Dao');

class OpenpearFavorite extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'favorite';
    
    protected $package_id;
    protected $maintainer_id;
    
    static protected $__package_id__ = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function getPackage(){
        if(is_object($this->package)) return $this->package;
        $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
        return $this->package;
    }
    protected function getMaintainer(){
        if(is_object($this->maintainer)) return $this->maintainer;
        $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
        return $this->maintainer;
    }
    protected function __after_save__(){
        $this->recount_favorites();
        OpenpearTimeline::add_favorite($this);
    }
    public function recount_favorites(){
        try {
            $fav_count = C(OpenpearFavorite)->find_count(Q::eq('package_id', $this->package_id()));
        } catch(Exception $e){
            $fav_count = 0;
        }
        $package = $this->package();
        $package->favored_count($fav_count);
        $package->save();
    }
}