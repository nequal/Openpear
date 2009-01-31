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
    function read(){
        $parser = parent::read(new Package(), new C(Q::pager(18)));
        return $parser;
    }
    function create(){
        $this->loginRequired();
        $parser = parent::create(new Package(), Rhaco::url('package/'. $this->getVariable('name', '')));
        $parser->setVariable('public', 1);
        return $parser;
    }
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

    function release($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u, true)){
            $baseinstalldir = '/';
            if(strpos($package, '_') !== false){
                $path = explode('_', $package);
                array_pop($path);
                $baseinstalldir .= implode('/', $path);
            }
            $release = new Release($package, $this->getVariable('package___l___baseinstalldir', $baseinstalldir));
            $default = empty($p->latestRelease) ? $release->get() : unserialize($p->latestRelease);

            $this->setVariable($default);
            $this->setVariable('object', $p);
            $this->setVariable('version', $p->getLatestVersion());
            return $this->parser('package/release.html');
        }
        return $this->_notFound();
    }
    function release_confirm($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if($this->isPost() && $this->isMaintainer($p, $u, true)){
            $release = $this->_getRelease($p);
            $this->clearVariable('pathinfo');
            $this->setVariable('vals', $this->getVariable());
            $this->setVariable('release', $release);
            $this->setVariable('object', $p);
            return $this->parser('package/release_confirm.html');
        }
        Header::redirect(Rhaco::url('package/'. $package. '/release'));
    }
    function release_do($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if($this->isPost() && $this->isMaintainer($p, $u, true)){
            $release = $this->_getRelease($p);
            if($release->build($package. '/'. $this->getVariable('build_path', 'trunk'))){
                $this->message('パッケージをリリースしました (version '.$this->getVariable('version___l___release_ver', '0.1.0').')');
                $p->setLatestRelease(serialize($release->get()));
                $this->dbUtil->update($p);
                $this->setVariable('buildLog', $release->buildLog);
                $this->setVariable('object', $p);
                return $this->parser('package/succeeed_release.html');
            }
            $this->message('build package failed.');
        }
        Header::redirect(Rhaco::url('package/'. $package. '/release'));
    }
    function _getRelease($p){
        $baseinstalldir = '/';
        if(strpos($p->name, '_') !== false){
            $path = explode('_', $p->name);
            array_pop($path);
            $baseinstalldir .= implode('/', $path);
        }
        $release = new Release($p->name, $this->getVariable('package___l___baseinstalldir', $baseinstalldir));
        $default = empty($p->latestRelease) ? $release->get() : unserialize($p->latestRelease);

        $variables = $this->getVariable();
        foreach($variables as $name => $value){
            if(strpos($name, '___l___') === false) continue;
            list($cat, $name) = explode('___l___', $name, 2);
            if(empty($name)) continue;
            $release->set($cat, $name, $value);
        }
        foreach($p->maintainers as $maintainer){
            $release->addMaintainer($maintainer->name, $maintainer->fullname, $maintainer->mail, $maintainer->role);
        }
        $release->description = $p->description;
        return $release;
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

