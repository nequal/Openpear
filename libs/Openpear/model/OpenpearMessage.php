<?php
require_once 'HatenaSyntax.php';
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.mail.Gmail');

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
    static protected $__maintainer_from_id__ = 'type=number';
    static protected $__subject__ = 'type=string,require=true';
    static protected $__description__ = 'type=text,require=true';
    static protected $__unread__ = 'type=boolean';
    static protected $__type__ = 'type=choice(system,notice,warning,normal)';
    static protected $__created__ = 'type=timestamp';
    
    protected $maintainer_to;
    protected $maintainer_from;
    static protected $__maintainer_to__ = 'type=OpenpearMaintainer,extra=true';
    static protected $__maintainer_from__ = 'type=OpenpearMaintainer,extra=true';
    
    public function __init__(){
        $this->created = time();
        $this->unread = true;
        $this->type = 'normal';
    }
    protected function __save_verify__(){
        if($this->type() !== 'system' && $this->isMaintainer_from_id()){
            Exceptions::add(new RequiredDaoException('maintainer_from_id required'), 'maintainer_from_id');
        }
    }
    protected function __after_create__(){
        $mail = new Gmail(def('gmail_account'), def('gmail_password'));
        $mail->to($this->maintainer_to()->mail());
        $mail->from(def('gmail_account'), 'Openpear');
        $mail->subject($this->subject());
        $mail->html($this->fmDescription());
        $mail->send();
    }
    
    public function permission(OpenpearMaintainer $maintainer){
        if($this->maintainer_to_id() === $maintainer->id()
            || $this->maintainer_from_id() === $maintainer->id()){
            return true;
        }
        return false;
    }
    protected function formatDescription(){
        return HatenaSyntax::render($this->description());
    }
    protected function __str__(){
        return $this->subject();
    }
    protected function getMaintainer_to(){
        if(is_object($this->maintainer_to)) return $this->maintainer_to;
        $this->maintainer_to = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_to_id()));
        return $this->maintainer_to;
    }
    protected function getMaintainer_from(){
        if(is_object($this->maintainer_from)) return $this->maintainer_from;
        $this->maintainer_from = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_from_id()));
        return $this->maintainer_from;
    }
}