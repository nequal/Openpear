<?php
import('org.rhaco.service.OpenIDAuth');

class AccountView extends Openpear
{
    /**
     * 新規登録フォーム
     */
    public function signup(){
        if($this->inSessions('openid_identity')){
            $this->vars('openid', true);
            $this->vars('openid_identity', $this->inSessions('openid_identity'));
        } else $this->vars('openid', false);
        if(!$this->isPost()){
            $this->cp(R(OpenpearMaintainer));
        }
        $this->template('account/signup.html');
        return $this;
    }
    /**
     * 新規登録を実行する
     */
    public function signup_do(){
        if($this->isPost()){
            $account = new OpenpearMaintainer();
            try {
                $account->set_vars($this->vars());
                $account->new_password($this->inVars('new_password'));
                $account->new_password_conf($this->inVars('new_password_conf'));
                $account->save();
                if($this->isSessions('openid_identity')){
                    $openid_maintainer = new OpenpearOpenidMaintainer();
                    $openid_maintainer->maintainer_id($account->id());
                    $openid_maintainer->url($this->inSessions('openid_identity'));
                    $openid_maintainer->save();
                    $this->rmSessions('openid_identity');
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
        if($this->isLogin()) Http::redirect(url('dashboard'));
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
        if($this->isLogin()) Http::redirect(url('dashboard'));
        if(OpenIDAuth::login($openid_user, $this->inVars('openid_url'))){
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
