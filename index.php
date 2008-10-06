<?php
/**
 * index
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
require_once '__init__.php';
Rhaco::import('generic.Urls');
Rhaco::import('model.LoginCondition');
Rhaco::import('Openpear');
Rhaco::import('OpenpearPackage');
Rhaco::import('OpenpearMaintainer');

RequestLogin::silent(new LoginCondition());

$db = new DbUtil(Package::connection());
$parser = Urls::parser(array(
    '^$' => array(
        'class' => 'Openpear',
        'method' => 'index',
    ),
    '^mypage$' => array(
        'class' => 'Openpear',
        'method' => 'mypage',
    ),
    '^login$' => array(
        'class' => 'Openpear',
        'method' => 'login',
    ),
    '^logout$' => array(
        'class' => 'Openpear',
        'method' => 'logout',
    ),

    '^package$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'read',
    ),
    '^packages\/create$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'create',
    ),
    '^packages\/search$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'search',
    ),
    '^packages\/favorite$' => array(
        'OpenpearAPI',
        'toggleFavorite',
    ),
    '^package\/(.*?)\/settings$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'settings',
    ),
    '^package\/(.*?)\/release$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'release',
    ),
    '^package\/(.*?)\/maintainer$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'maintainer',
    ),
    '^package\/(.*?)\/maintainer\/add$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'maintainer_add',
    ),
    '^package\/(.*?)\/maintainer\/update$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'maintainer_update',
    ),
    '^package\/(.*?)\/maintainer\/remove$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'maintainer_remove',
    ),
    '^package\/(.+)$' => array(
        'class' => 'OpenpearPackage',
        'method' => 'detail',
    ),

    '^maintainer$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'read',
    ),
    '^maintainer\/signup$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'signup',
    ),
    '^maintainer\/settings$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'settings',
    ),
    '^maintainer\/add_openid$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'addOpenId',
    ),
    '^maintainer\/delete_openid$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'deleteOpenId',
    ),
    '^maintainer\/(.+)$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'detail',
    ),
    
    '^api\/maintainers$' => array(
        'class' => 'OpenpearAPI',
        'method' => 'maintainers',
    ),
), $db);
$request = new Request();
$parser->setFilter('filter.OpenpearTemplateFilter');
$parser->setVariable('isLogin', RequestLogin::isLoginSession());
if(RequestLogin::isLoginSession()) $parser->setVariable('my', RequestLogin::getLoginSession());
if($request->isSession('message')){
	$parser->setVariable('message', $request->getSession('message'));
	$request->clearSession('message');
}
$parser->write();

