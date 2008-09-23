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
        return $parser;
    }
    function search(){
        if($this->isVariable('w')){
            $parser = parent::read(new Package(), new C(Q::ilike(Package::columnDescription(), $this->getVariable('w'), 'p'), Q::orderDesc(Package::columnUpdated()), Q::pager(18)));
        } else return $this->read();
    }
    function detail($name){
        $parser = parent::detail(new Package(), new C(Q::eq(Package::columnName(), $name), Q::depend()));
        $parser->setVariable('latestVersion', $this->getLastestVersion($name, 'no release'));
        if(RequestLogin::isLogin()){
            $u = RequestLogin::getLoginSession();
            $p = $parser->variables['object'];
            $parser->setVariable('isMaintainer', $this->isMaintainer($p, $u));
        }
        return $parser;
    }

    // == ==

    function release($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u)){
            // fixme
            $default = array(
                'version' => '0.1.0',
                'stability' => 'stable',
                'license_name' => 'New BSD License',
                'license_url' => 'http://creativecommons.org/licenses/BSD/',
                'php_min' => '4.3.3',
                'pear_min' => '1.4.0',
            );
            $parser = new HtmlParser('package/release.html');
            if($this->isPost()){
                $release = new Release($package);
                $release->setVersion($this->getVariable('version', $default['version']), $this->getVariable('stability', $default['stability']));
                $release->setLicense($this->getVariable('license_name', $default['license_name']), $this->getVariable('license_url', $default['license_url']));
                $release->setMin($this->getVariable('php_min', $default['php_min']), $this->getVariable('pear_min', $default['pear_min']));
                foreach($p->maintainers as $maintainer){
                    $release->addMaintainer($maintainer->name, $maintainer->fullname, $maintainer->mail, $maintainer->role);
                }
                $release->build($this->getVariable('build_path', $package.'/trunk'));
                Rhaco::end();// debug.
            } else $parser->setVariable($default);
            $parser->setVariable('object', $p);
            $parser->setVariable('version', $this->getLastestVersion($package, '0.1.0'));
            return $parser;
        }
        return $this->_notFound();
    }
    function settings($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u)){
            $this->clearVariable('name', 'created');
            $parser = parent::update(new Package(), new C(Q::eq(Package::columnId(), $p->id)), Rhaco::url('package/'.$p->getName()));
            return $parser;
        }
        return $this->_notFound();
    }

    function getLastestVersion($package, $default='0.1.0'){
        static $db = null;
        Rhaco::import('model.ServerPackages');
        if(!Variable::istype('DbUtil', $db)) $db = new DbUtil(ServerPackages::connection());
        $stabs = array();
        $lastest = $default;
        $releases = $db->select(new ServerPackages(), new C(Q::eq(ServerPackages::columnName(), $package)));
        foreach($releases as $release){
            $stab = unserialize($release->stability);
            if (!isset($stabs[$stab['release']]) || -1==version_compare($stabs[$stab['release']], $release->version)) {
                $stabs[$stab['release']] = $release->version;
            }
            if (-1==version_compare($stabs['lastest'], $release->version)) {
                $stabs['lastest'] = $item['version'];
            }
        }
        if(isset($stabs['lastest']))
            $lastest = $stabs['lastest'];
        return $lastest;
    }
}

