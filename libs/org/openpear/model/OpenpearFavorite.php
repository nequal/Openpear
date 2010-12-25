<?php
import('org.rhaco.storage.db.Dao');

/**
 * Favorites
 *
 * @var integer $package_id @{"require":true,"primary":true}
 * @var integer $maintainer_id @{"require":true,"primary":true}
 */
class OpenpearFavorite extends Dao
{
    protected $package_id;
    protected $maintainer_id;
    
    private $package;
    private $maintainer;
    
    public function package(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = OpenpearPackage::get_package($this->package_id());
            }catch(Exception $e){}
        }
        return $this->package;
    }
    public function maintainer(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = OpenpearMaintainer::get_maintainer($this->maintainer_id());
            }catch(Exception $e){}
        }
        return $this->maintainer;
    }
    protected function __after_save__(){
        self::recount_favorites($this->package_id());
        C($this)->commit();

        $timeline = new OpenpearTimeline();
        $timeline->subject(sprintf('<a href="%s">%s</a> <span class="hl">liked</span> <a href="%s">%s</a>',
            url('maintainer/'. $this->maintainer()->name()),
            R(Templf)->htmlencode(str($this->maintainer())),
            url('package/'. $this->package()->name()),
            $this->package()->name()
        ));
        $timeline->description(sprintf('<a href="%s">%s</a>: latest %s. %d fans.',
            url('package/'. $this->package()->name()),
            $this->package()->name(),
            $this->package()->latest_release()->fm_version(),
            C(OpenpearFavorite)->find_count(Q::eq('package_id', $this->package_id()))
        ));
        $timeline->type('favorite');
        $timeline->package_id($this->package_id());
        $timeline->maintainer_id($this->maintainer_id());
        $timeline->save();
    }
    static public function recount_favorites($package_id){
        try {
            $fav_count = C(OpenpearFavorite)->find_count(Q::eq('package_id', $package_id));
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $package_id));
            $package->favored_count($fav_count);
            $package->save();
        } catch(Exception $e){}
    }
}
