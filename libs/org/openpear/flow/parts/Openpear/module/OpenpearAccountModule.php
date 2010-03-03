<?php
module('model.OpenpearMaintainer');

class OpenpearAccountModule extends Object
{
    public function login_condition(Request $request){
        if($request->user() instanceof OpenpearMaintainer){
            return true;
        }
        if($request->is_post()){
            try{
                $user = C(OpenpearMaintainer)->find_get(
		                    Q::ob(
		                    	Q::b(Q::eq('name', $request->in_vars('login')))
		                    	,Q::b(Q::eq('mail', $request->in_vars('mail')))
		                    )
		                );
	            if(is_object($user)){
	            	if(!$user->certify($request->in_vars('password'))){
		                Exceptions::add(new Exception('password is incorrect'), 'password');
		            }else{
			            $request->user($user);
			            return true;
		            }
	            }
            } catch(Exception $e){
                Exceptions::add($e, 'mail');
            }
        }
        return false;
    }
}