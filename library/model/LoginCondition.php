<?php
/**
 * LoginCondition
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('network.http.Header');

class LoginCondition
{
    function condition($request){
        return RequestLogin::isLogin();
    }

    function invalid(){
        Header::redirect(Rhaco::url('login'));
    }
}


