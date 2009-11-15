<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');
import('jp.nequal.pear.PackageProjector');

class OpenpearRelease extends Dao implements AtomInterface
{
    protected $_table_ = 'release';
    
    protected $id;
    protected $package_id;
    protected $maintainer_id;
    protected $version;
    protected $version_stab;
    protected $notes;
    protected $settings;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__  = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__version__ = 'type=string,require=true';
    static protected $__version_stab__ = 'type=choice(stable,beta,alpha),require=true';
    static protected $__notes__ = 'type=text';
    static protected $__settings__ = 'type=text';
    static protected $__created__ = 'type=timestamp';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    public function __init__(){
        $this->version = '1.0.0';
        $this->version_stab = 'stable';
        $this->created = time();
    }
    
    protected function formatVersion(){
        if(is_null($this->id)) return 'No Release';
        if($this->version_stab === 'stable') return $this->version();
        return sprintf('%s (%s)', $this->version, $this->version_stab);
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
        return $this->package()->name();
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
    /** @todo */
    public function atom_content(){
        return sprintf('%s is released!', $this->package()->name());
    }
    public function atom_summary(){
        return sprintf('%s is released!', $this->package()->name());
    }
    public function atom_author(){
        return str($this->maintainer());
    }
    public function atom_href(){
        
    }
}