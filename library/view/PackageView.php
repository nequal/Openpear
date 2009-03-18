<?php
/**
 * PackageView
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('model.Release');
Rhaco::import('view.ViewBase');

class PackageView extends ViewBase
{
    /**
     * プロジェクト一覧
     */
    function read(){
        $parser = parent::read(new Package(), new C(Q::pager(18)));
        return $parser;
    }
    /**
     * プロジェクト作成
     */
    function create(){
        $this->loginRequired();
        $parser = parent::create(new Package(), Rhaco::url('package/'. $this->getVariable('name', '')));
        $parser->setVariable('public', 1);
        return $parser;
    }
    /**
     * プロジェクト詳細
     */
    function detail($name){
        $parser = parent::detail(new Package(), new C(Q::eq(Package::columnName(), $name), Q::depend()));
        if(!isset($parser->variables['object'])) return $parser;
        $p = $parser->variables['object'];
        $parser->setVariable('latestVersion', $p->getLatestVersion('no release'));
        if(RequestLogin::isLoginSession()){
            $u = RequestLogin::getLoginSession();
            $parser->setVariable('isMaintainer', $this->isMaintainer($p, $u, true));
        }
        return $parser;
    }
    /**
     * リリースキューに登録
     */
    function release($packageName){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $package = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $packageName)));
        if(!Variable::istype('Package', $package)){
            return $this->_notFound();
        }
        if($package->isMaintainer($this->dbUtil, $u, true)){
            return $this->confirmedCreate(new ReleaseQueue(), Rhaco::url('url/'. $package->name));
        }
        return $this->_forbidden();
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

