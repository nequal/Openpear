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
        if(RequestLogin::isLoginSession()){
            $this->message('既にログインしています');
            Header::redirect(Rhaco::url('mypage'));
        }
        $url = $this->openIdLogin();
        if($url == false) {
            return new HtmlParser('login.html');
        }
        $account = $this->dbUtil->get(new OpenId(), new C(Q::eq(OpenId::columnUrl(), $url), Q::fact()));
        if(Variable::istype('OpenId', $account) && Variable::istype('Maintainer', $account->factMaintainer)){
            $this->message('ログインしました');
            RequestLogin::setLoginSession($account->factMaintainer);
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
    function logout(){
        $this->loginRequired();
        RequestLogin::logout();
        $this->message('ログアウトしました');
        Header::redirect(Rhaco::url());
    }

    function openIdLogin($endPoint='login'){
        if($this->isVariable('openid_identity')){
            // validate & create user
            $openid = new OpenIDAuth();
            $variables = $this->getVariable();
            if($openid->validate($variables)){
                return $this->getVariable('openid_identity');
            }
        }
        if($this->isVariable('server')){
            // redirect
            $parser = new HtmlParser('login/redirect.html');
            $openid = new OpenIDAuth($this->getVariable('server'));
            $openid->request();
            $endPointURL = $openid->getEndPointURL();
            if(empty($endPointURL)) return false;
            $openid->addParameter('openid.sreg.required', 'nickname');
            $openid->addParameter('openid.sreg.optional', 'email');
            $openid->addParameter('openid.identity', 'http://specs.openid.net/auth/2.0/identifier_select');
            $openid->addParameter('openid.claimed_id', 'http://specs.openid.net/auth/2.0/identifier_select');
            $parser->setVariable('url', $endPointURL);
            $parser->setVariable('headers', $openid->getEndPointHeaders(Rhaco::url(), Rhaco::url($endPoint)));
            $parser->write();
            Rhaco::end();
        }
        return false;
    }

    function isMaintainer($package, $maintainer, $strict=false){
        if($strict == false && $package->isPublic()){
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
        if(!Variable::istype('DbUtil', $db)){
            Rhaco::import('model.ServerPackages');
            Rhaco::import('model.ServerMaintainers');
            Rhaco::import('model.ServerCategories');
            $db = new DbUtil(ServerPackages::connection());
        }
        return $db;
    }

    function message($message, $e=false){
        if($e === true && ExceptionTrigger::isException()){
            $messages = array($message);
            foreach(ExceptionTrigger::get() as $e){
                $messages[] = $e->getMessage();
            }
            $message = implode('<br />', $messages);
        }
        $this->setSession('message', $message);
    }
    function json($data, $allowCallback=true){
        $json = json_encode($data);
        if($allowCallback === true && $this->isVariable('callback')){
            Header::write(array('Content-type' => 'text/javascript; charset=utf-8'));
            printf('%s(%s);', $this->getVariable('callback'), $json);
        } else {
            Header::write(array('Content-type' => 'application/json; charset=utf-8', 'X-JSON' => $json));
            echo $json;
        }
        Rhaco::end();
    }
    function xml($data, $name='result'){
        $xml = Variable::toSimpleTag($name, $data);
        if(empty($xml)){
            Http::status(404);
        } else {
            Header::write(array('Content-type' => 'text/xml; charset=utf-8'));
            echo $xml->get(true);
        }
        Rhaco::end();
    }
    function loginRequired(){
        RequestLogin::loginRequired(new LoginCondition());
        if(!RequestLogin::isLoginSession()){
            $this->message('ログインが必要な動作です');
            Header::redirect(Rhaco::url('login'));
        }
    }
    function _notFound(){
        parent::_notFound();
        $this->setTemplate('error/404.html');
        return $this->parser();
    }
    function _forbidden(){
        Http::status(403);
        $this->setTemplate('error/403.html');
        return $this->parser();
    }
}

