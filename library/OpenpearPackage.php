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
            $parser->setVariable('isMaintainer', $this->isMaintainer($p, $u));
        }
        return $parser;
    }

    // == ==

    function maintainer($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package), Q::depend()));
        if($this->isMaintainer($p, $u)){
            $parser = new HtmlParser('package/maintainer.html');
            $parser->setVariable('object', $p);
            return $parser;
        } else $this->_forbidden();
    }
    function maintainer_add($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        $response = array('error' => 1, 'message' => 'unknown error');
        if($this->isPost() && $this->isVariable('maintainer') && $this->isVariable('role')){
            if($this->isMaintainer($p, $u)){
                $maintainer = $this->dbUtil->get(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $this->getVariable('maintainer'))));
                if(Variable::istype('Maintainer', $maintainer)){
                    $response = array('error' => 1, 'message' => 'unknown error');
                    $isMaintainer = $this->dbUtil->get(new Charge(), new C(Q::eq(Package::columnId(), $p->id), Q::eq(Maintainer::columnId(), $maintainer->id)));
                    if(Variable::istype('Charge', $isMaintainer)){
                        $response = array('error' => 1, 'message' => 'already exists maintainer');
                    }
                    $charge = new Charge();
                    $charge->setMaintainer($maintainer->id);
                    $charge->setPackage($p->id);
                    $charge->setRole($this->getVariable('role', 'lead'));
                    if($charge->save($this->dbUtil)){
                        $response = array('error' => 0, 'success' => 1, 'maintainer' => array('name' => $maintainer->name, 'fullname' => $maintainer->fullname, 'role' => $charge->role));
                    }
                } else $response = array('error' => 1, 'message' => 'unknown maintainer');
            } else $response = array('error' => 1, 'message' => 'forbidden');
        }
        $this->json($response);
    }
    function maintainer_update($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        $response = array('error' => 1, 'message' => 'unknown error');
        if($this->isPost() && $this->isVariable('id') && $this->isVariable('role')){
            if($this->isMaintainer($p, $u)){
            } else $response = array('error' => 1, 'message' => 'forbidden');
        }
        $this->json($response);
    }
    function maintainer_delete($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();
        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        $response = array('error' => 1, 'message' => 'unknown error');
        if($this->isPost() && $this->isVariable('id')){
            if($this->isMaintainer($p, $u)){
                $mc = $this->dbUtil->count(new Charge(), new C(Q::eq(Package::columnId(), $p->id)));
                if($mc > 1){
                    // delete
                } else $response = array('error', 'message' => 'maintainer is required');
            } else $response = array('error' => 1, 'message' => 'forbidden');
        }
        $this->json($response);
    }

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
                'build_path' => 'trunk',
                'baseinstalldir' => '/',
            );
            if(strpos($package, '_') !== false){
                $path = explode('_', $package);
                array_pop($path);
                $default['baseinstalldir'] .= implode('/', $path);
            }
            $parser = new HtmlParser('package/release.html');
            if($this->isPost()){
                $release = new Release($package, $this->getVariable('baseinstalldir', $default['baseinstalldir']));
                $release->setVersion($this->getVariable('version', $default['version']), $this->getVariable('stability', $default['stability']));
                $release->setLicense($this->getVariable('license_name', $default['license_name']), $this->getVariable('license_url', $default['license_url']));
                $release->setMin($this->getVariable('php_min', $default['php_min']), $this->getVariable('pear_min', $default['pear_min']));
                foreach($p->maintainers as $maintainer){
                    $release->addMaintainer($maintainer->name, $maintainer->fullname, $maintainer->mail, $maintainer->role);
                }
                $release->description = $p->description;
                if($release->build($package. '/'. $this->getVariable('build_path', $default['build_path']))){
                    Header::redirect(Rhaco::url('package/').$package);
                }
                echo nl2br($release->buildLog);
                Rhaco::end();// debug.
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
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u)){
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

