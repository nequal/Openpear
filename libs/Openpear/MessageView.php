<?php

class MessageView extends Openpear
{
    public function model($id){
        $this->_login_required();
        $user = $this->user();
        try {
            $message = C(OpenpearMessage)->find_get(Q::eq('id', $id));
            if($massage->hasPermisson($user)){
                if($message->maintainer_to_id() === $user->id()){
                    $message->unread(false);
                    $message->save(true);
                }
                $this->vars('object', $message);
                return $this;
            }
        } catch(Exception $e){
            Exceptions::add($e);
        }
        $this->fail_redirect();
    }
    /**
     * 受信箱
     */
    public function inbox(){
        $this->_login_required();
        $user = $this->getUser();
        $paginator = new Paginator(20, $this->inVars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
            $paginator, Q::eq('maintainer_to_id', $user->id()), Q::order('-id')
        ));
        return $this;
    }
    /**
     * 送信したメッセージ
     */
    public function sentbox(){
        $this->_login_required();
        $user = $this->getUser();
        $paginator = new Paginator(20, $this->inVars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
           $paginator, Q::eq('maintainer_from_id', $user->id()), Q::order('-id')
        ));
        return $this;
    }
    /**
     * 送信します
     */
    public function compose(){
        $this->_login_required();
        return $this;
    }
    public function send_confirm(){
        $this->_login_required();
        if($this->isPost()){
            try {
                $message = new OpenpearMessage();
                $message->set_vars($this->vars());
                $message->save(false);
                return $this;
            } catch(Exception $e){}
        }
        return $this->compose();
    }
    public function send_do(){
        $this->_login_required();
        if($this->isPost()){
            $message = new OpenpearMessage();
            $message->set_vars($this->vars());
            $message->save(true);
            $this->success_redirect();
        }
        $this->fail_redirect();
    }
}