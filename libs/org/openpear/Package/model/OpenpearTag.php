<?php
import('org.rhaco.storage.db.Dao');

class OpenpearTag extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'tag';
    
    protected $id;
    protected $name;
    protected $prime;
    protected $package_count;
    
    static protected $__id__ = 'type=serial';
    static protected $__name__ = 'type=string,unique=true,require=true';
    static protected $__prime__ = 'type=boolean';
    static protected $__package_count__ = 'type=number';
    
    protected $packages;
    static protected $__packages__ = 'type=OpenpearPackage[],extra=true';
    
    protected function __init__(){
        $this->prime = false;
        $this->package_count = 0;
    }
    protected function __str__(){
        return $this->name();
    }
    protected function __get_package__s(){
        if(!empty($this->packages)) return $this->packages;
        $packages = array();
        try {
            $package_tags = C(OpenpearPackageTag)->find_all(Q::eq('tag_id', $this->id()));
            foreach($package_tags as $package_tag){
                $packages[] = $package_tag->package();
            }
        } catch(Exception $e){}
        $this->packages = $packages;
        return $packages;
    }
}