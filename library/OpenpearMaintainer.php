<?php
/**
 * OpenpearMaintainer
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */

class OpenpearMaintainer extends Openpear
{
    function signup(){
        if($this->isPost() && $this->isSession('openId')){
            $maintainer = $this->toObject(new Maintainer());
            if($this->dbUtil->insert($maintainer)){
                $openId = new OpenId();
                $openId->setUrl($this->getSession('openId'));
                $openId->setMaintainer($maintainer->id);
                $openId->save($this->dbUtil);
                $this->clearSession('openId');
                $this->message('ユーザー登録が完了しました');
                RequestLogin::setLoginSession($maintainer);
                Header::redirect(Rhaco::url('mypage'));
            }
        }
        Header::redirect(Rhaco::url('login'));
    }
    function read(){
        $parser = parent::read(new Maintainer(), new C(Q::pager(18)));
        return $parser;
    }
    function detail($name){
        $parser = parent::detail(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $name), Q::depend()));
        return $parser;
    }
    function settings(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $this->clearVariable('id', 'openId', 'open_id', 'name', 'created');
        $parser = parent::update(new Maintainer(), new C(Q::eq(Maintainer::columnId(), $u->id)), Rhaco::url('mypage'));
        return $parser;
    }
    function addOpenId(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $url = $this->openIdLogin('maintainer/add_openid');
        if($url == false){
            $parser = new HtmlParser('maintainer/addOpenId.html');
            $parser->setVariable('object_list', $this->dbUtil->select(new OpenId(), new C(Q::eq(OpenId::columnMaintainer(), $u->id))));
            return $parser;
        }
        $openId = $this->dbUtil->get(new OpenId(), new C(Q::eq(OpenId::columnUrl(), $url)));
        if(Variable::istype('OpenId', $openId)){
        	$this->message('既に登録されている OpenID です');
        	Header::redirect(Rhaco::url('maintainer/add_openid'));
        }
        $openId = new OpenId();
        $openId->setUrl($url);
        $openId->setMaintainer($u->id);
        if($this->dbUtil->insert($openId)){
            $this->message('OpenID を追加しました');
            Header::redirect(Rhaco::url('maintainer/add_openid'));
        }
        $this->message('OpenID の追加に失敗しました', true);
        Header::redirect(Rhaco::url('maintainer/add_openid'));
    }
    function deleteOpenId(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        if($this->isPost() && $this->isVariable('url')){
            $openId = $this->dbUtil->get(new OpenId(), new C(Q::eq(OpenId::columnUrl(), $this->getVariable('url')), Q::eq(OpenId::columnMaintainer(), $u->id)));
            if(Variable::istype('OpenId', $openId) && $this->dbUtil->delete($openId)){
                $this->message('OpenID を削除しました');
                Header::redirect(Rhaco::url('maintainer/add_openid'));
            }
        }
        $this->message('OpenID の削除に失敗しました', true);
        Header::redirect(Rhaco::url('maintainer/add_openid'));
    }
}

