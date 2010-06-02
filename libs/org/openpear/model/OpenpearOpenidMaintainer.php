<?php
import('org.rhaco.storage.db.Dao');

class OpenpearOpenidMaintainer extends Dao
{
    protected $maintainer_id;
    protected $url;
    
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    static protected $__url__ = 'type=string,require=true,primary=true';
    
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
