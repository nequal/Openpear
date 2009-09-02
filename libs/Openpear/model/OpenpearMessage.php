<?php
import('org.rhaco.storage.db.Dao');

class OpenpearMessage extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'message';
    
    protected $id;
    protected $maintainer_to_id;
    protected $maintainer_from_id;
    protected $subject;
    protected $description;
    protected $unread;
    protected $type;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__maintainer_to_id__ = 'type=number,require=true';
    static protected $__maintainer_from_id__ = 'type=number,require=true';
    static protected $__subject__ = 'type=string,require=true';
    static protected $__description__ = 'type=text,require=true';
    static protected $__unread__ = 'type=boolean';
    static protected $__type__ = 'type=choice(notice,warning,normal)';
    static protected $__created__ = 'type=timestamp';
    
    protected $maintainer_to;
    protected $maintainer_from;
    static protected $__maintainer_to__ = 'type=OpenpearMaintainer,cond=maintainer_to_id()id';
    static protected $__maintainer_from__ = 'type=OpenpearMaintainer,cond=maintainer_from_id()id';
    
    
    public function __init__(){
        $this->created = time();
        $this->type = 'normal';
    }
    
    public function hasPermission(OpenpearMaintainer $maintainer){
        if($this->maintainer_to_id() === $maintainer->id()
            || $this->maintainer_from_id() === $maintainer->id()){
            return true;
        }
        return false;
    }
    
    protected function __str__(){
        return $this->subject();
    }
}