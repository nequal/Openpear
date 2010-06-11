<?php
import('org.openpear.model.OpenpearMaintainer');

class OpenpearAccountModule extends Object
{
    public function login_condition(Request $request){
        if ($request->user() instanceof OpenpearMaintainer) {
            return true;
        }
        if ($request->is_post() && $request->is_vars('login') && $request->is_vars('password')) {
            try {
                $user = C(OpenpearMaintainer)->find_get(
		                    Q::ob(
		                    	Q::b(Q::eq('name', $request->in_vars('login')))
		                    	,Q::b(Q::eq('mail', $request->in_vars('login')))
		                    )
		                );
	            if ($user instanceof OpenpearMaintainer) {
	            	if ($user->certify($request->in_vars('password'))) {
			            $request->user($user);
			            return true;
		            } else {
		                Exceptions::add(new Exception('password is incorrect'), 'password');
		            }
	            }
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        return false;
    }
}
