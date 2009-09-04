<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');

class OpenpearTimeline extends Dao implements AtomInterface
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'timeline';
    
    protected $id;
    protected $subject;
    protected $description;
    protected $type;
    protected $package_id;
    protected $maintainer_id;
    protected $created;
    static protected $__id__ = 'type=serial';
    static protected $__subject__ = 'type=string,require=true';
    static protected $__description__ = 'type=text,require=true';
    static protected $__type__ = 'type=choice(release,changeset,user_activities,package_setting,favorite)';
    static protected $__package_id__ = 'type=number';
    static protected $__maintainer_id__ = 'type=number';
    static protected $__created__ = 'type=timestamp';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function __init__(){
        $this->created = time();
    }
    
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
    
    public function atom_id(){
        return $this->id();
    }
    public function atom_title(){
        return $this->subject();
    }
    public function atom_published(){
        return $this->created();
    }
    public function atom_updated(){
        return $this->created();
    }
    public function atom_issued(){
        return $this->created();
    }
    public function atom_content(){
        return $this->description();
    }
    public function atom_summary(){
        return $this->description();
    }
    public function atom_author(){
        if($this->maintainer() instanceof OpenpearMaintainer){
            return str($this->maintainer());
        }
        return 'Openpear';
    }
    public function atom_href(){
        if($this->package() instanceof OpenpearPackage){
            return url('package/'. $this->package->name());
        } else if ($this->maintainer() instanceof OpenpearMaintainer){
            return url('maintainer/'. $this->maintainer->name());
        }
    }
}