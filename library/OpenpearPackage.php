<?php
/**
 * OpenpearPackage
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('model.Release');

class OpenpearPackage extends Openpear
{
    function read(){
        $parser = parent::read(new Package(), new C(Q::orderDesc(Package::columnUpdated()), Q::pager(18)));
        return $parser;
    }
    function create(){
        $this->loginRequired();
        $parser = parent::create(new Package(), Rhaco::url('package/'. $this->getVariable('name', '')));
        $parser->setVariable('public', 1);
        return $parser;
    }
    function search(){
        if($this->isVariable('w')){
            $parser = parent::read(new Package(), new C(Q::ilike(Package::columnDescription(), $this->getVariable('w'), 'p'), Q::orderDesc(Package::columnUpdated()), Q::pager(18)));
        } else return $this->read();
    }
    function detail($name){
        $parser = parent::detail(new Package(), new C(Q::eq(Package::columnName(), $name), Q::depend()));
        if(!isset($parser->variables['object'])) return $parser;
        $parser->setVariable('latestVersion', $this->getLatestVersion($name, 'no release'));
        if(RequestLogin::isLoginSession()){
            $u = RequestLogin::getLoginSession();
            $p = $parser->variables['object'];
            $parser->setVariable('isMaintainer', $this->isMaintainer($p, $u, true));
        }
        return $parser;
    }

    // == ==

    function maintainer($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if($this->isMaintainer($p, $u, true)){
            $parser = new HtmlParser('package/maintainer.html');
            $parser->setVariable('object', $p);
            return $parser;
        } else return $this->_forbidden();
    }
    function maintainer_add($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if($this->isPost() && $this->isVariable('maintainer') && $this->isVariable('role')){
            if($this->isMaintainer($p, $u, true)){
                $maintainer = $this->dbUtil->get(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $this->getVariable('maintainer'))));
                if(Variable::istype('Maintainer', $maintainer)){
                    if($this->isMaintainer($p, $maintainer)){
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
    function maintainer_update($package){
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
    function maintainer_remove($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if($this->isPost() && $this->isVariable('id') && $this->isMaintainer($p, $u, true)){
            $charge = $this->dbUtil->get(new Charge(), new C(Q::eq(Charge::columnPackage(), $p->id), Q::eq(Charge::columnMaintainer(), $this->getVariable('id'))));
            if(Variable::istype('Charge', $charge)){
                $mc = $this->dbUtil->count(new Charge(), new C(Q::eq(Package::columnId(), $p->id)));
                if($mc > 1){
                    if($this->dbUtil->delete($charge)){
                        $this->message('メンテナの解除を行いました');
                        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
                    }
                }
            }
        }
        $this->message('メンテナの解除に失敗しました', true);
        Header::redirect(Rhaco::url('package/'). $p->name. '/maintainer');
    }

    function release($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u, true)){
            if(strpos($package, '_') !== false){
                $path = explode('_', $package);
                array_pop($path);
                $baseinstalldir .= implode('/', $path);
            }
            $release = new Release($package, $this->getVariable('package|baseinstalldir', $baseinstalldir));
            $default = empty($p->latestRelase) ? $release->get() : unserialize($p->latestRelease);

            $parser = new HtmlParser('package/release.html');
            if($this->isPost()){
                $variables = $this->getVariable();
                foreach($variables as $name => $value){
                    if(strpos($name, '|') === false) continue;
                    list($cat, $name) = explode('|', $name, 2);
                    $release->set($cat, $name, $value);
                }
                foreach($p->maintainers as $maintainer){
                    $release->addMaintainer($maintainer->name, $maintainer->fullname, $maintainer->mail, $maintainer->role);
                }
                $release->description = $p->description;
                if($release->build($package. '/'. $this->getVariable('build_path', 'trunk'))){
                    $this->message('パッケージをリリースしました (version '.$this->getVariable('version|release_ver', '0.1.0').')');
                    Header::redirect(Rhaco::url('package/').$package);
                }
                $p->setLatestRelease(serialize($release->get()));
                $this->setTemplate('package/succeeed_release.html');
                $this->setVariable('buildLog', $release->buildLog);
                $this->setVariable('object', $p);
                return $this->parser();
            } else $parser->setVariable($default);
            $parser->setVariable('object', $p);
            $parser->setVariable('version', $this->getLatestVersion($package, '0.1.0'));
            return $parser;
        }
        return $this->_notFound();
    }
    function settings($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u, true)){
            $this->clearVariable('name', 'created');
            $parser = parent::update(new Package(), new C(Q::eq(Package::columnId(), $p->id)), Rhaco::url('package/'.$p->getName()));
            return $parser;
        }
        return $this->_notFound();
    }

    function getLatestVersion($package, $default='0.1.0'){
        $db = $this->getServerDB();
        $stabs = array();
        $latest = $default;
        $releases = $db->select(new ServerPackages(), new C(Q::eq(ServerPackages::columnName(), $package)));
        foreach($releases as $release){
            $stab = unserialize($release->stability);
            if (!isset($stabs[$stab['release']]) || -1==version_compare($stabs[$stab['release']], $release->version)) {
                $stabs[$stab['release']] = $release->version;
            }
            if (-1==version_compare(@$stabs['latest'], $release->version)) {
                $stabs['latest'] = $release->version;
            }
        }
        if(isset($stabs['latest']))
            $latest = $stabs['latest'];
        return $latest;
    }
}

