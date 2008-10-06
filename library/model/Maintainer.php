<?php
Rhaco::import("model.table.MaintainerTable");
/**
 * 
 */
class Maintainer extends MaintainerTable{
    var $role = 'lead';

    function beforeInsert($db){
    	$denyNames = array('signup', 'settings', 'add_openid', 'delete_openid');
    	if(in_array($this->name, $denyNames)) return false;
    	return true;
    }

    function afterInsert($db){
    	$this->hashPassword();
        $this->updateAccountFile($db);
        return true;
    }
    function afterUpdate($db){
    	$this->hashPassword();
        $this->updateAccountFile($db);
        if(RequestLogin::isLoginSession()){
            $u = RequestLogin::getLoginSession();
            if($u->id == $this->id) RequestLogin::setLoginSession($this);
        }
        return true;
    }
    function afterDelete($db){
        $this->updateAccountFile($db);
        return true;
    }

    function hashPassword(){
    	$this->password = $this->_h($this->password);
    }

    function updateAccountFile($db){
        $accounts = array();
        $accounts[] = sprintf('%s:%s', Rhaco::constant('SYSTEM_USER'), Maintainer::_h(Rhaco::constant('SYSTEM_PASS')));
        $maintainers = $db->select(new Maintainer());
        foreach($maintainers as $maintainer){
            $accounts[] = sprintf('%s:%s', $maintainer->getName(), $maintainer->getPassword());
        }
        if(!empty($accounts) && count($accounts) > 1)
            file_put_contents(sprintf('%s/%s.passwd', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME')), implode("\n", $accounts));
    }

    function afterSelect($db){
        if(!empty($this->dependCharges)){
            $c = new C(Q::order(Package::columnName()));
            foreach($this->dependCharges as $charge){
                $c->addCriteriaOr(new C(Q::eq(Package::columnId(), $charge->package)));
            }
            $this->setPackages($db->select(new Package(), $c));
        }
    }

    function _h($p){
        $a = array(0,1,2,3,4,5,6,7,8,9,"a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
        $salt = $a[array_rand($a)] . $a[array_rand($a)];
        return crypt($p, $salt);
    }
}

?>
