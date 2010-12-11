<?php
import('org.rhaco.storage.db.Dao');

/**
 * OpenID Maintainer
 *
 * @var integer $maintainer_id @{"require":true,"primary":true}
 * @var string $url @{"require":true,"primary":true}
 */
class OpenpearOpenidMaintainer extends Dao
{
    protected $maintainer_id;
    protected $url;
    
    private $maintainer;
    
    public function maintainer(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
            }catch(Exception $e){}
        }
        return $this->maintainer;
    }
}
