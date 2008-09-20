<?php
Rhaco::import("model.table.ChargeTable");
/**
 * 
 */
class Charge extends ChargeTable{
    function afterUpdate(){

    }
    function afterDelete(){

    }

    function updateAccess($db){
        $filename = sprintf('%s/%s.access', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'));

        $packages = $db->select(new Package());
    }
}

?>
