<?php
import('org.rhaco.storage.db.Dao');

class OpenpearPackageMessage extends Dao
{
    protected $id;
    protected $package_id;
    protected $description;
    protected $unread;
    protected $type;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__ = 'type=integer,require=true';
    static protected $__description__ = 'type=text,require=true';
    static protected $__unread__ = 'type=boolean';
    static protected $__type__ = 'type=choice(maintainer,public)';
    static protected $__created__ = 'type=timestamp';
    
    protected function __init__(){
        $this->unread = true;
        $this->created = time();
        $this->type = 'maintainer';
    }
    protected function __fm_description__(){
        return HatenaSyntax::render($this->description());
    }
}
