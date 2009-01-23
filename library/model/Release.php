<?php
Rhaco::import("model.table.ReleaseTable");
Rhaco::import('tag.HtmlParser');
/**
 * 
 */
class Release extends ReleaseTable{
    /**
     * propertyをセットする。
     * ex) $release->setProp($request->getVariable());
     * 
     * @return  void
     */
    function setProp($param){
        // とりあえず全部つっこむ
        ObjectUtil::hashConvObject($param, $this);
        
        // めちゃEthnaっぽいwww
        // rhacoならdictつかえよwwww
        $prop_def = array(
            'role' => array(
                'ext' => array('required' => true),
                'type' => array('required' => true),
            ),
            'file' => array(
                'path' => array('required' => true),
                'commandscript', 'ignore', 'platform', 'install', 'role',
            ),
            'dep' => array(
                'type' => array('required' => true),
                'channel', 'min', 'max',
            ),
        );
        
        foreach($prop_def as $name => $prop){
            $tmp = array();
            if(isset($param[$name])){
                $params = $param[$name];
                if(is_array($params)){
                    foreach($params as $val){
                        $tmpv = array();
                        foreach($prop as $k => $p){
                            if(is_numeric($k)) $k = $p;
                            if(isset($p['required']) && $p['required'] == true && (!isset($val[$k])) || empty($val[$k]))
                                return ExceptionTrigger::raise(new GenericException('hmm...'));
                            if(isset($val[$k]) && !empty($val[$k])) $tmpv[$k] = $val[$k];
                        }
                        if(!empty($tmpv)) $tmp[] = $tmpv;
                    }
                }
            }
            $this->$name = serialize($tmp);
        }
        
        $installer = array();
        if(isset($param['installer'])){
            // あとまわしw
        }
        $this->installer = serialize($installer);
    }
    function verify(&$db){
        $maintainers = $this->getMaintainerList($db);
        $bool = false;
        foreach($maintainers as $maintainer) if($maintainer->role == 'lead') $bool = true;
        if($bool === false) return ExceptionTrigger::raise(new GenericException('lead maintainer required'));
    }
    
    function readBuildConf(&$db){
        $parser = new HtmlParser('system/build.conf');
        $parser->setVariable('release', $this);
        $parser->setVariable('db', $db);
        return $parser->read();
    }
    
    function getRoleList(){
        return unserialize($this->role);
    }
    /**
     * maintainer リスト
     */
    function getMaintainerList(&$db){
        $charge = $db->select(new Charge(), new C(Q::eq(Charge::columnPackage(), $this->package), Q::fact()));
        $maintainers = array();
        foreach($charge as $c){
            $maintainer = $c->factMaintainer;
            $maintainer->role = $c->role;
            $maintainers[] = $maintainer;
        }
        return $maintainers;
    }
    function getFileList(){
        return unserialize($this->file);
    }
}

?>