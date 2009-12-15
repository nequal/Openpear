<?php

class OpenpearAccountModule extends Object
{
    public function login_condition(Request $request){
        if($request->user() instanceof OpenpearMaintainer){
            return true;
        }
        if($request->is_post()){
            try{
                $user = C(OpenpearMaintainer)->find_get(
                    Q::eq('mail', $request->in_vars('mail')),
                    Q::or_block(Q::eq('name', $request->in_vars('login')))
                );
            } catch(Exception $e){
                Exceptions::add($e, 'mail');
            }
            if(is_object($user) && !$user->certify($request->in_vars('password'))){
                Exceptions::add(new Exception('password is incorrect'), 'password');
            }
            Exceptions::validation();
            $request->user($user);
            return true;
        }
        return false;
    }
    public function login_invalid(Request $request){
    }
    public function after_login(Request $request){
    }
    public function before_logout(Request $request){
    }
}