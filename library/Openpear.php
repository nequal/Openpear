<?php
/**
 * Openpear
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('generic.Views');
Rhaco::import('OpenIDAuth');

class Openpear extends Views
{
    function index(){
        $parser = new HtmlParser('index.html');
        return $parser;
    }
    function mypage(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $packages = $this->dbUtil->select(new Package(), new C(Q::eq(Package::columnId(), Charge::columnPackage()), Q::eq(Charge::columnMaintainer(), $u->getId())));
        $parser = new HtmlParser('mypage.html');
        $parser->setVariable('object', $u);
        $parser->setVariable('packages', $packages);
        return $parser;
    }

    function login(){
        if($this->isVariable('openid_identity')){
            // validate & create user
            $openid = new OpenIDAuth();
            $variables = $this->getVariable();
            if($openid->validate($variables)){
                $url = $this->getVariable('openid_identity');
                // maintainer exists?
                $maintainer = $this->dbUtil->get(new Maintainer(), new C(Q::eq(Maintainer::columnOpenId(), $url)));
                if(Variable::istype('Maintainer', $maintainer)){
                    RequestLogin::setLoginSession($maintainer);
                    Header::redirect(Rhaco::url('mypage'));
                } else {
                    // create maintainer
                    $this->setSession('openId', $url);
                    $parser = new HtmlParser('maintainer/signup.html');
                    $parser->setVariable($this->getVariable());
                    $parser->setVariable('openId', $url);
                    return $parser;
                }
            }
        }
        if($this->isVariable('server')){
            // redirect
            $parser = new HtmlParser('login/redirect.html');
            $openid = new OpenIDAuth($this->getVariable('server'));
            $openid->request();
            $endPointURL = $openid->getEndPointURL();
            if(empty($endPointURL)) return $this->_notFound();
            $openid->addParameter('openid.sreg.required', 'nickname');
            $openid->addParameter('openid.sreg.optional', 'email');
            $openid->addParameter('openid.identity', 'http://specs.openid.net/auth/2.0/identifier_select');
            $openid->addParameter('openid.claimed_id', 'http://specs.openid.net/auth/2.0/identifier_select');
            $parser->setVariable('url', $endPointURL);
            $parser->setVariable('headers', $openid->getEndPointHeaders(Rhaco::url(), Rhaco::url('login')));
            return $parser;
        }
        return new HtmlParser('login.html');
    }
    function logout(){
        $this->loginRequired();
        RequestLogin::logout();
        Header::redirect(Rhaco::url());
    }

    function isMaintainer($package, $maintainer){
        if($package->isPublic()){
            return true;
        }
        $charge = $this->dbUtil->get(new Charge(), new C(Q::eq(Charge::columnPackage(), $package->getId()), Q::eq(Charge::columnMaintainer(), $maintainer->getId())));
        if(Variable::istype('Charge', $charge)){
            return true;
        }
        return false;
    }

    function getServerDB(){
        static $db = null;
        Rhaco::import('model.ServerPackages');
        Rhaco::import('model.ServerMaintainers');
        Rhaco::import('model.ServerCategories');
        if(!Variable::istype('DbUtil', $db)) $db = new DbUtil(ServerPackages::connection());
        return $db;
    }

    function message($message){
    	$this->setSession('message', $message);
    }
    function json($data, $callback=null){
        $json = json_encode($data);
        if(is_null($callback)){
            Header::write(array('Content-type' => 'application/json; charset=utf-8', 'X-JSON' => $json));
            echo $json;
        } else {
            Header::write(array('Content-type' => 'text/javascript; charset=utf-8'));
        	printf('%s(%s);', $callback, $json);
        }
        Rhaco::end();
    }
    function loginRequired(){
        RequestLogin::loginRequired(new LoginCondition());
        if(!RequestLogin::isLoginSession()){
            Header::redirect(Rhaco::url('login'));
        }
    }
    function _notFound(){
        Http::status(404);
        parent::_notFound();
        $parser = $this->parser();
        $parser->setTemplate('error/404.html');
        return $parser;
    }
    function _forbidden(){
        $this->_notFound();//fixme!
    }
}

