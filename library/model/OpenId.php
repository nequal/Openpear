<?php
Rhaco::import("model.table.OpenIdTable");
/**
 * 
 */
class OpenId extends OpenIdTable{
    function beforeDelete($db){
    	if($db->count(new OpenId(), new C(OpenId::columnMaintainer(), $this->maintainer)) <= 1){
    		return false;
    	}
    }
}

?>