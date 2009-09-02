<?php
import('org.rhaco.storage.db.Dao');

class OpenpearCharge extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'charge';
    
    protected $package_id;
    protected $maintainer_id;
    protected $role;
    
    static protected $__package_id__ = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__role__ = 'type=choice(lead,developer,contributor,helper),require=true';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPacakge,extra=true';
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
}
