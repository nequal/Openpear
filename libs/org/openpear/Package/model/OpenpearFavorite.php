<?php
import('org.rhaco.storage.db.Dao');

class OpenpearFavorite extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'favorite';
    
    protected $package_id;
    protected $maintainer_id;
    
    static protected $__package_id__ = 'type=number,require=true,primary=true';
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function __get_package__(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
            }catch(Exception $e){}
        }
        return $this->package;
    }
    protected function __get_maintainer__(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
            }catch(Exception $e){}
        }
        return $this->maintainer;
    }
    protected function __after_save__(){
        $this->recount_favorites();
        $timeline = new OpenpearTimeline();
        $timeline->subject(sprintf('<a href="%s">%s</a> <span class="hl">liked</span> <a href="%s">%s</a>',
            url('maintainer/'. $this->maintainer()->name()),
            C(Templf)->htmlencode(str($this->maintainer())),
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
