<?php
import('org.rhaco.service.OpenIDAuth');
import('org.openpear.Openpear.OpenpearFlow');
import('org.openpear.Maintainer.model.OpenpearMaintainer');
import('org.openpear.Maintainer.model.OpenpearOpenidMaintainer');

class Account extends OpenpearFlow
{
    /**
     * 新規登録フォーム
     */
    public function signup(){
        if($this->in_sessions('openid_identity')){
            $this->vars('openid', true);
            $this->vars('openid_identity', $this->in_sessions('openid_identity'));
        } else $this->vars('openid', false);
        if(!$this->is_post()){
            $this->cp(R(OpenpearMaintainer));
        }
        $this->template('account/signup.html');
        return $this;
    }
    /**
     * 新規登録を実行する
     */
    public function signup_do(){
        if($this->is_post()){
            $account = new OpenpearMaintainer();
            try {
                $account->set_vars($this->vars());
                $account->new_password($this->in_vars('new_password'));
                $account->new_password_conf($this->in_vars('new_password_conf'));
                $account->save();
                if($this->is_sessions('openid_identity')){
                    $openid_maintainer = new OpenpearOpenidMaintainer();
                    $openid_maintainer->maintainer_id($account->id());
                    $openid_maintainer->url($this->in_sessions('openid_identity'));
                    $openid_maintainer->save();
                    $this->rm_sessions('openid_identity');
                }
                C($account)->commit();
            } catch(Exception $e){
                Exceptions::add($e);
                return $this->signup();
            }
            $this->user($account);
            parent::login();
            $this->success_redirect();
        }
        $this->fail_redirect();
    }
    /**
     * パスワードでログインする
     */
    public function login(){
        if($this->is_login()) Http::redirect(url('dashboard'));
        try {
            if(parent::login()){
                // TODO: 任意の転送先を設定できるようにする
                $this->success_redirect();
            }
        } catch(Exception $e){}
        $this->template('account/login.html');
        return $this;
    }
    /**
     * OpenID でログインする
     */
    public function login_by_openid(){
        if($this->is_login()) Http::redirect(url('dashboard'));
        if(OpenIDAuth::login($openid_user, $this->in_vars('openid_url'))){
            try {
                $openid_maintainer = C(OpenpearOpenidMaintainer)->find_get(Q::eq('url', $openid_user->identity()));
                $this->user($openid_maintainer->maintainer());
                if(parent::login()){
                    $this->success_redirect();
                }
            } catch(Exception $e){
                Exceptions::add($e);
                $this->sessions('openid_identity', $openid_user->identity());
                Http::redirect(url('account/signup'));
            }
        }
        return $this->login();
    }
    /**
     * ログアウトする
     */
    public function logout(){
        parent::logout();
        $this->success_redirect();
    }
}
