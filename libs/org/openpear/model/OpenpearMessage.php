<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.mail.Gmail');
import('org.yabeken.service.Pea');
/**
 * 
 * @const account gmailアカウント,パスワード
 */
class OpenpearMessage extends Dao
{
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
    static protected $__type__ = 'type=choice(system,system_notice,notice,warning,normal)';
    static protected $__created__ = 'type=timestamp';
    
    protected $mail = true;
    private $maintainer_to;
    private $maintainer_from;
    static protected $__mail__ = 'type=boolean,extra=true';
    
    protected function __init__(){
        $this->created = time();
        $this->unread = true;
        $this->type = 'normal';
    }
    protected function __fm_unread__(){
    	return ($this->unread) ? "unread" : "";
    }
    protected function __save_verify__(){
        if(!($this->type() === 'system' || $this->type() === 'system_notice') && $this->is_maintainer_from_id()){
            Exceptions::add(new OpenpearException('maintainer_from_id required'), 'maintainer_from_id');
        }
    }
    protected function __after_create__(){
        if($this->mail()){
            list($account,$password) = OpenpearConfig::gmail_account();
            $mail = new Gmail($account,$password);
            $mail->to($this->maintainer_to()->mail());
            $mail->from($mail->from(), 'Openpear');
            $mail->subject($this->subject());
            $mail->message(strip_tags($this->fm_description()));
            $mail->send();
        }
    }
    
    public function permission(OpenpearMaintainer $maintainer){
        if($this->maintainer_to_id() === $maintainer->id()
            || $this->maintainer_from_id() === $maintainer->id()){
            return true;
        }
        return false;
    }
    
    public function maintainer_to(){
        if($this->maintainer_to instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer_to = OpenpearMaintainer::get_maintainer($this->maintainer_to_id());
            }catch(Exception $e){}
        }
        return $this->maintainer_to;
    }
    public function maintainer_from(){
        if($this->maintainer_from instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer_from = OpenpearMaintainer::get_maintainer($this->maintainer_from_id());
            }catch(Exception $e){}
        }
        return $this->maintainer_from;
    }
    
    protected function __fm_description__(){
        return HatenaSyntax::render($this->description());
    }
    protected function __str__(){
        return $this->subject();
    }
}
