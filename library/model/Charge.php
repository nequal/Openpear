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
    function beforeDelete($db){
        $package_members = $db->count(new Charge(), new C(Q::eq(Charge::columnPackage(), $this->package)));
        if($package_members < 2){
            return ExceptionTrigger::raise(new GenericException('package required maintainers'));
        }
        return true;
    }
    function afterDelete($db){
        $this->updateAccess($db);
        return true;
    }

    function updateAccess($db){
        $filename = Rhaco::constant('SVN_ACCESS_FILE', '/home/openpear/svn/openpear.access');
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
