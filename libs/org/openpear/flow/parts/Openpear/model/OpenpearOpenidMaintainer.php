<?php
import('org.rhaco.storage.db.Dao');
module('model.OpenpearMaintainer');

class OpenpearOpenidMaintainer extends Dao
{
    protected $maintainer_id;
    protected $url;
    
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    static protected $__url__ = 'type=string,require=true,primary=true';
    
    protected $maintainer;
    static protected $__maintainer__ = 'type=OpenpearMaintainer,cond=maintainer_id()id';
}