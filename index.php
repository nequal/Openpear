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
    '^maintainer\/(.+)$' => array(
        'class' => 'OpenpearMaintainer',
        'method' => 'detail',
    ),
), $db);
$parser->setFilter('filter.OpenpearTemplateFilter');
$parser->setVariable('isLogin', RequestLogin::isLoginSession());
if(RequestLogin::isLoginSession()) $parser->setVariable('my', RequestLogin::getLoginSession());
$parser->write();

