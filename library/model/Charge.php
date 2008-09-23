<?php
Rhaco::import("model.table.ChargeTable");
/**
 * 
 */
class Charge extends ChargeTable{
    function afterInsert($db){
        $this->updateAccess($db);
        return true;
    }
    function afterUpdate($db){
        $this->updateAccess($db);
        return true;
    }
    function afterDelete($db){
        $this->updateAccess($db);
        return true;
    }

    function updateAccess($db){
        $filename = sprintf('%s/%s.access', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'));
        $maintainers = $db->select(new Maintainer());
        $developers = array();
        foreach($maintainers as $maintainer) $developers[] = $maintainer->name;
        $access = "[groups]\n";
        $access .= "developer = ". implode(', ', $developers). "\n\n";
        
        $access .= "[/]\n";
        $access .= "* = r\n\n";

        $packages = $db->select(new Package(), new C(Q::depend()));
        foreach($packages as $package){
            $access .= sprintf("[/%s]\n", $package->name);
            $access .= "* = r\n";
            if($package->isPublic()){
                $access .= "@developer = rw\n";
            } else {
                foreach($package->maintainers as $maintainer){
                    $access .= sprintf("%s = rw\n", $maintainer->name);
                }
            }
            $access .= "\n";
        }
        return file_put_contents($filename, $access) !== false;
    }
}

?>
