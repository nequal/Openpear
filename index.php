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

RequestLogin::silent(new LoginCondition());

$db = new DbUtil(Package::connection());
$parser = Urls::parser(array(
    '^$' => array(
        'class' => 'view.ViewBase',
        'method' => 'index',
    ),
    '^mypage$' => array(
        'class' => 'view.ViewBase',
        'method' => 'mypage',
    ),
    '^login$' => array(
        'class' => 'view.ViewBase',
        'method' => 'login',
    ),
    '^logout$' => array(
        'class' => 'view.ViewBase',
        'method' => 'logout',
    ),

    '^package$' => array(
        'class' => 'view.PackageView',
        'method' => 'read',
    ),
    '^packages\/create$' => array(
        'class' => 'view.PackageView',
        'method' => 'create',
    ),
    '^packages\/search$' => array(
        'class' => 'view.PackageView',
        'method' => 'search',
    ),
    '^packages\/favorite$' => array(
        'class' => 'view.APIView',
        'method' => 'toggleFavorite',
    ),
    '^package\/(.*?)\/settings$' => array(
        'class' => 'view.PackageView',
        'method' => 'settings',
    ),
    '^package\/(.*?)\/release$' => array(
        'class' => 'view.PackageView',
        'method' => 'release',
    ),
    '^package\/(.*?)\/release\/confirm$' => array(
        'class' => 'view.PackageView',
        'method' => 'release_confirm',
    ),
    '^package\/(.*?)\/release\/do$' => array(
        'class' => 'view.PackageView',
        'method' => 'release_do',
    ),
    '^package\/(.*?)\/maintainer$' => array(
        'class' => 'view.PackageMaintainerView',
        'method' => 'read',
    ),
    '^package\/(.*?)\/maintainer\/add$' => array(
        'class' => 'view.PackageMaintainerView',
        'method' => 'add',
    ),
    '^package\/(.*?)\/maintainer\/update$' => array(
        'class' => 'view.PackageMaintainerView',
        'method' => 'update',
    ),
    '^package\/(.*?)\/maintainer\/remove$' => array(
        'class' => 'view.PackageMaintainerView',
        'method' => 'remove',
    ),
    '^package\/(.+)$' => array(
        'class' => 'view.PackageView',
        'method' => 'detail',
    ),

    '^maintainer$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'read',
    ),
    '^maintainer\/signup$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'signup',
    ),
    '^maintainer\/settings$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'settings',
    ),
    '^maintainer\/add_openid$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'addOpenId',
    ),
    '^maintainer\/delete_openid$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'deleteOpenId',
    ),
    '^maintainer\/(.+)/timeline$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'timeline',
    ),
    '^maintainer\/(.+)$' => array(
        'class' => 'view.MaintainerView',
        'method' => 'detail',
    ),
    
    '^repository(\/.*?)?$' => array(
        'class' => 'view.RepositoryView',
        'method' => 'browse'
    ),
    '^changeset\/(\d+)$' => array(
        'class' => 'view.RepositoryView',
        'method' => 'changeset',
    ),
    
    '^api\/maintainers$' => array(
        'class' => 'view.APIView',
        'method' => 'maintainers',
    ),
    
    '^feed\/package\/new$' => array(
        'class' => 'view.APIView',
        'method' => 'feedNewPackage',
    ),
    '^feed\/package\/update$' => array(
        'class' => 'view.APIView',
        'method' => 'feedUpdatePackage',
    ),
    '^feed\/repository$' => array(
        'class' => 'view.APIView',
        'method' => 'feedRepository',
    ),
    
    '^(.+?)$' => array(
        'class' => 'view.StaticView',
        'method' => 'page',
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
$parser->setVariable('of', Rhaco::obj('util.OpenpearFormatter'));
$parser->write();

