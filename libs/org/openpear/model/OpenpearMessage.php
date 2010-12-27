<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.mail.Gmail');
/**
 * Message
 *
 * @var serial $id
 * @var integer $maintainer_to_id @{"require":true}
 * @var integer $maintainer_from_id
 * @var string $subject @{"require":true}
 * @var text $description @{"require":true}
 * @var boolean $unread
 * @var choice $type @{"choices":["system","system_notice","notice","warning","normal"]}
 * @var timestamp $created
 * @var boolean $mail @{"extra":true}
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
    protected $type = 'normal';
    protected $created;

    protected $mail = true;
    private $maintainer_to;
    private $maintainer_from;

    static public function unread_count(OpenpearMaintainer $maintainer) {
        $key = array('openpear_message_unread', $maintainer->id());
        if (Store::has($key)) {
            return Store::get($key);
        }
        $unread_messages_count = C(__CLASS__)->find_count(Q::eq('maintainer_to_id', $maintainer->id()), Q::eq('unread', true));
        Store::set($key, $unread_messages_count);
        return $unread_messages_count;
    }

    public function mark_all_as_read(OpenpearMaintainer $maintainer) {
        foreach (C(__CLASS__)->find_all(Q::eq('maintainer_to_id', $maintainer->id())) as $message) {
            $message->unread(false);
            $message->save();
        }
    }

    public function permission(OpenpearMaintainer $maintainer, $throw = false){
        if($this->maintainer_to_id() == $maintainer->id()
            || $this->maintainer_from_id() == $maintainer->id()){
            return true;
        }
        if ($throw) throw new OpenpearException('permission denied');
        return false;
    }

    protected function __init__(){
        $this->created = time();
        $this->unread = true;
    }
    protected function __fm_unread__(){
    	return ($this->unread) ? "unread" : "";
    }
    protected function __save_verify__(){
        if($this->type() !== 'system' && $this->type() !== 'system_notice' && !$this->is_maintainer_from_id()){
            Exceptions::add(new OpenpearException('maintainer_from_id required'), 'maintainer_from_id');
        }
    }
    protected function __after_create__(){
        try {
            if($this->mail()){
                list($account, $password) = OpenpearConfig::gmail_account();
                $mail = new Gmail($account, $password);
                $mail->to($this->maintainer_to()->mail(), str($this->maintainer_to()));
                $mail->from($mail->from(), 'Openpear');
                $mail->subject($this->subject());
                $mail->message(strip_tags($this->fm_description()));
                $mail->send();
            }
        } catch (Exception $e) {
            Log::debug($e);
        }
    }
    protected function __after_save__() {
        foreach (array($this->maintainer_to_id, $this->maintainer_from_id) as $id) {
            Store::delete(array('openpear_message_unread', $id));
        }
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
