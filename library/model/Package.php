<?php
Rhaco::import("model.table.PackageTable");
Rhaco::import('SvnUtil');
/**
 * 
 */
class Package extends PackageTable{
    function afterInsert($db){
        // add charge
        $m = RequestLogin::getLoginSession();
        $charge = new Charge();
        $charge->setMaintainer($m->id);
        $charge->setPackage($this->id);
        if(!$db->insert($charge)) return false;

        // create repository
        $wp = Rhaco::constant('WORKING_DIR');
        $path = sprintf('file://%s/%s/%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $this->name);
        $svn = new SvnUtil();
        $svn->cmd(sprintf('mkdir %s -m "[Add Package] %s"', $path, $this->name));
	$svn->_cmd('rm -rf '. $wp);
        $svn->cmd(sprintf('co %s %s', $path, $wp));
        FileUtil::mkdir($wp.'/trunk');
        FileUtil::mkdir($wp.'/tags');
	$svn->cmd('add '.$wp.'/trunk '.$wp.'/tags');
        $svn->cmd(sprintf('ci %s -m "[Create Base Directory] %s"', $wp, $this->name));
    }

    function afterSelect($db){
        if(!empty($this->dependCharges)){
            $c = new C();
            foreach($this->dependCharges as $charge){
                $c->addCriteriaOr(new C(Q::eq(Maintainer::columnId(), $charge->maintainer)));
            }
            $this->setMaintainers($db->select(new Maintainer(), $c));
        }
    }
}

?>
