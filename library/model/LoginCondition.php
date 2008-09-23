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
        return Variable::istype('Maintainer', RequestLogin::getLoginSession());
    }

    function invalid(){
        Header::redirect(Rhaco::url('login'));
    }
}


