<?php
Rhaco::import("model.table.PackageTable");
Rhaco::import('model.NewprojectQueue');
/**
 * 
 */
class Package extends PackageTable{
    var $favorites;
    
    /**
     * メンテナかどうかを判定する
     *
     * @param   DbUtil      $db
     * @param   Maintainer  $maintainer メンテナモデル
     * @param   bool        $strict     メンテナリストに登録されているかをチェック
     * @return  bool
     */
    function isMaintainer(&$db, $maintainer, $strict=false){
        if($strict == false && $this->isPublic()){
            return true;
        }
        $charge = $db->get(new Charge(), new C(Q::eq(Charge::columnPackage(), $this->getId()), Q::eq(Charge::columnMaintainer(), $maintainer->getId())));
        if(Variable::istype('Charge', $charge)){
            return true;
        }
        return false;
    }
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
        
        $queue = new NewprojectQueue();
        $queue->setMaintainer($m->id);
        $queue->setPackage($this->id);
        $db->insert($queue);
        
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
