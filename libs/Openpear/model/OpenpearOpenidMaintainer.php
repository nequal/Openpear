<?php
import('org.rhaco.storage.db.Dao');

class OpenpearOpenidMaintainer extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'openid_maintainer';
    
    protected $maintainer_id;
    protected $url;
    
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    static protected $__url__ = 'type=string,require=true,primary=true';
    
    protected $maintainer;
    static protected $__maintainer__ = 'type=OpenpearMaintainer,cond=maintainer_id()id';
}