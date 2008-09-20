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
        if($this->isPost() && $this->isSession('open_id')){
            $maintainer = $this->toObject(new Maintainer());
            $maintainer->setOpenId($this->getSession('open_id'));
            if($this->dbUtil->insert($maintainer)){
                $this->clearSession('open_id');
                Header::redirect(Rhaco::url('mypage'));
            }
        }
        Header::redirect(Rhaco::url('login'));
    }
    function read(){
        $parser = parent::read(new Maintainer(), new C(Q::order(Maintainer::columnName()), Q::pager(99)));
        return $parser;
    }
    function detail($name){
        $parser = parent::detail(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $name), Q::depend()));
        return $parser;
    }
    function settings(){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $this->clearVariable('id', 'name', 'created');
        $parser = parent::update($u, Rhaco::url('mypage'));
        return $parser;
    }
}

