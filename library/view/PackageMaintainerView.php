<?php
/**
 * PackageMaintainerView
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('model.Release');
Rhaco::import('view.ViewBase');

class PackageMaintainerView extends ViewBase
{
    function read($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if($this->isMaintainer($p, $u, true)){
            $parser = new HtmlParser('package/maintainer.html');
            $parser->setVariable('object', $p);
            return $parser;
        } else return $this->_forbidden();
    }
    function add($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if($this->isPost() && $this->isVariable('maintainer') && $this->isVariable('role')){
            if($this->isMaintainer($p, $u, true)){
                $maintainer = $this->dbUtil->get(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $this->getVariable('maintainer'))));
                if(Variable::istype('Maintainer', $maintainer)){
                    if($this->isMaintainer($p, $maintainer, true)){
                        $this->message('既にメンテナ登録されています');
                        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
                    }
                    $charge = new Charge();
                    $charge->setMaintainer($maintainer->id);
                    $charge->setPackage($p->id);
                    $charge->setRole($this->getVariable('role', 'lead'));
                    if($charge->save($this->dbUtil)){
                        $this->message('メンテナを追加しました');
                        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
                    }
                }
            }
        }
        $this->message('メンテナの追加に失敗しました', true);
        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
    }
    function update($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if($this->isPost() && $this->isVariable('id') && $this->isVariable('role') && $this->isMaintainer($p, $u, true)){
            $charge = $this->dbUtil->get(new Charge(), new C(Q::eq(Charge::columnPackage(), $p->id), Q::eq(Charge::columnMaintainer(), $this->getVariable('id'))));
            if(Variable::istype('Charge', $charge)){
                $charge->setRole($this->getVariable('role'));
                if($charge->save($this->dbUtil)){
                    $this->message('メンテナの状態を変更しました');
                    Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
                }
            }
        }
        $this->message('メンテナ状態の変更に失敗しました', true);
        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
    }
    function remove($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if(!Variable::istype('Package', $p)){
            return $this->_notFound();
        }
        if($this->isPost() && $this->isVariable('id') && $p->isMaintainer($this->dbUtil, $u, true)){
            if($this->dbUtil->delete(new Charge(), new C(Q::eq(Charge::columnPackage(), $p->id), Q::eq(Charge::columnMaintainer(), $this->getVariable('id'))))){
                // success
                Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
            }
        }
        ExceptionTrigger::raise(new GenericException('remove the maintainer failed'));
        return $this->read($package);
    }
}

