<?php
Rhaco::import("model.table.PackageTable");
Rhaco::import('SvnUtil');
/**
 * 
 */
class Package extends PackageTable{
    var $favorites;
    
    function getLatestVersion($default='0.1.0'){
        $lr = unserialize($this->latestRelease);
        return isset($lr['version___l___release_ver']) ? $lr['version___l___release_ver'] : $default;
    }
    
    function beforeInsert(){
        $this->created = $this->updated = time();
        return true;
    }
    function afterInsert($db){
        // add charge
        $m = RequestLogin::getLoginSession();
        $charge = new Charge();
        $charge->setMaintainer($m->id);
        $charge->setPackage($this->id);
        if(!$db->insert($charge)) return false;

        // create repository
        $wp = Rhaco::constant('WORKING_DIR'). '/NEWREP'. md5($this->name);
        $path = sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), $this->name);
        $svn = new SvnUtil();
        $svn->cmd(sprintf('mkdir %s -m "[Add Package] %s"', $path, $this->name));
        $svn->_cmd('rm -rf '. $wp);
        $svn->cmd(sprintf('co %s %s', $path, $wp));
        FileUtil::mkdir($wp.'/trunk');
        FileUtil::mkdir($wp.'/tags');
        $svn->cmd('add '.$wp.'/trunk '.$wp.'/tags');
        $svn->cmd(sprintf('ci %s -m "[Create Base Directory] %s"', $wp, $this->name));
        return true;
    }
    function beforeUpdate(){
        $this->updated = time();
        return true;
    }
    function afterSelect($db){
        if(!empty($this->dependCharges)){
            $c = new C(Q::order(Maintainer::columnName()));
            $roles = array();
            foreach($this->dependCharges as $charge){
                $c->addCriteriaOr(new C(Q::eq(Maintainer::columnId(), $charge->maintainer)));
                $roles[$charge->maintainer] = $charge->role;
            }
            $maintainers = $db->select(new Maintainer(), $c);
            foreach($maintainers as &$maintainer){
                if(isset($roles[$maintainer->id])) $maintainer->role = $roles[$maintainer->id];
            }
            $this->setMaintainers($maintainers);
        }
        if(!empty($this->dependFavorites)){
            $c = new C();
            foreach($this->dependFavorites as $favorite){
                $c->addCriteriaOr(new C(Q::eq(Maintainer::columnId(), $favorite->maintainer)));
            }
            $this->favorites = $db->select(new Maintainer(), $c);
        }
    }
    function views(){
        return array(
            'search_fields' => 'name,description',
            'ordering' => '-updated',
        );
    }
}

?>
