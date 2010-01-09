<?php
class OpenpearFlow extends Flow
{
    protected function __new__($dict=null){
        Log::debug($dict);
        parent::__new__($dict);
        $this->o('Template')->statics('ot', 'org.openpear.Openpear.OpenpearTemplf');
    }
    /**
     * Json で出力する
     */
    protected function json_response($values){
        header('Content-type: application/json; charset=utf-8');
        if(is_array($values) || is_object($values)){
            if($values instanceof Object){
                echo json_encode($values->hash());
            } else {
                echo json_encode($values);
            }
        } else {
            echo (string)$values;
        }
        exit;
    }
    
    protected function _login_required($redirect_to=null){
        if($this->is_login()){
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
    
    /**
     * テンプレートだけ表示したい（要らないだろうけど，いずれなんかの情報を与えたい）
     */
    /**public function static(){
        return $this;
    }
    */
}
import('org.openpear.Maintainer');
