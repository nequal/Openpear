<?php
/**
 * OpenpearPackage
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
require_once 'PEAR/Server2.php';
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
        $server = $this->_getServer();
        $parser->setVariable('latestVersion', $server->backend->searchLastestVersion($name));
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
            if($this->isPost()){
                $release = new Release($package);
                $release->setVersion($this->getVariable('version', '0.1.0'), $this->getVariable('stability', 'stable'));
                $release->setLicense($this->getVariable('license_name', 'New BSD License'), $this->getVariable('license_url', 'http://creativecommons.org/licenses/BSD/'));
                $release->setMin($this->getVariable('php_min', '4.3.3'), $this->getVariable('pear_min', '1.4.0'));
                foreach($p->maintainers as $maintainer){
                    $release->addMaintainer($maintainer->name, $maintainer->fullname, $maintainer->mail, $maintainer->role);
                }
                $release->build($this->getVariable('build_path', $package.'/trunk'));
            } else {
                $server = $this->_getServer();
                $parser = new HtmlParser('package/release.html');
                $parser->setVariable('object', $p);
                $parser->setVariable('version', $server->backend->searchLastestVersion($package));
                return $parser;
            }
        }
        return $this->_notFound();
    }
    function settings($package){
        $this->loginRequired();
        $u = RequestLogin::getLoginSession();

        $p = $this->dbUtil->get(new Package(), new C(Q::eq(Package::columnName(), $package)));
        if(Variable::istype('Package', $p) && $this->isMaintainer($p, $u)){
            $this->clearVariable('name', 'created');
            $parser = parent::update($p, Rhaco::url('package/'.$p->getName()));
            return $parser;
        }
        return $this->_notFound();
    }
    function _getServer(){
        static $server;
        if(is_object($server)) return $server;
        $server = new PEAR_Server2(include(Rhaco::path('channel.config.php')));
        return $server;
    }
}

