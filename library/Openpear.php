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
                    Header::redirect($this->getVariable('return_to', Rhaco::url()));
                } else {
                    // create maintainer
                    $this->setSession('open_id', $url);
                    $parser = new HtmlParser('maintainer/signup.html');
                    $parser->setVariable($this->getVariable());
                    $parser->setVariable('open_id', $url);
                    return $parser;
                }
            }
        }
        if($this->isVariable('server')){
            // redirect
            $parser = new HtmlParser('login/redirect.html');
            $openid = new OpenIDAuth($this->getVariable('server'));
            $openid->request();
            $openid->addParameter('openid.sreg.required', 'nickname');
            $openid->addParameter('openid.sreg.optional', 'email');
            $openid->addParameter('openid.identity', 'http://specs.openid.net/auth/2.0/identifier_select');
            $openid->addParameter('openid.claimed_id', 'http://specs.openid.net/auth/2.0/identifier_select');
            $parser->setVariable('url', $openid->getEndPointURL());
            $parser->setVariable('headers', $openid->getEndPointHeaders(Rhaco::url(), Rhaco::url('login')));
            return $parser;
        }
        return new HtmlParser('login.html');
    }
    function logout(){
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

    function loginRequired(){
        RequestLogin::loginRequired(new LoginCondition());
    }
    function _notFound(){
        parent::_notFound();
        return $this->parser();
    }
}

