<?php
import('org.openpear.Openpear.exception.OpenpearException');
import('org.openpear.Maintainer.model.OpenpearMaintainer');

class OpenpearFlow extends Flow
{
    protected function __new__($dict=null){
        $this->dict($dict);
        parent::__new__();
        $this->m('Template')->statics('ot', 'org.openpear.Openpear.OpenpearTemplf');
    }
    
    protected function _login_required($redirect_to=null){
        if($this->isLogin()){
            return ;
        }
        if($redirect_to === null){
            Http::redirect(url('account/login'));
        }
        Http::redirect(url('account/login?redirect_to='. url($redirect_to)));
    }
    
    /**
     * @todo
     */
    protected function _not_found(){
        Http::status_header(404);
        exit;
    }
    
    static protected function set_extra_objects($objects){
        if(is_object($objects)){
            $objects->set_extra_objects();
            return $objects;
        } else if(is_array($objects)){
            foreach($objects as &$object){
                if(is_object($object)) $object->set_extra_objects();
            }
            return $objects;
        }
        return $objects;
    }
    
    /**
     * テンプレートだけ表示したい（要らないだろうけど，いずれなんかの情報を与えたい）
     */
    /**public function static(){
        return $this;
    }
    */
}
