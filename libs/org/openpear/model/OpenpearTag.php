<?php
import('org.rhaco.storage.db.Dao');

/**
 * Tag
 *
 * @var serial $id
 * @var string $name @{"unique":true,"require":true}
 * @var boolean $prime
 * @var integer $package_count
 */
class OpenpearTag extends Dao
{
    protected $id;
    protected $name;
    protected $prime;
    protected $package_count;
    
    private $packages;
    
    protected function __init__(){
        $this->prime = false;
        $this->package_count = 0;
    }
    protected function __str__(){
        return $this->name();
    }
    public function packages(){
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
