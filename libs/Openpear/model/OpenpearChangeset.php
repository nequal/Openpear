<?php
import('org.rhaco.storage.db.Dao');

class OpenpearChangeset extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'changeset';
    
    protected $revision;
    protected $maintainer_id;
    protected $package_id;
    
    static protected $__revision__ = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__package_id__ = 'type=number,require=true';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,cond=package_id()id';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,cond=maintainer_id()id';
}