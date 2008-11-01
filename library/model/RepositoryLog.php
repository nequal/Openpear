<?php
Rhaco::import("model.table.RepositoryLogTable");
Rhaco::import('SvnUtil');
/**
 * 
 */
class RepositoryLog extends RepositoryLogTable{
    function parseSvnlookChanged($lines){
        $result = array();
        foreach($lines as $line){
            if(empty($line)) continue;
            $result[] = array(
                'status' => substr($line, 0, 2),
                'type' => (trim(substr($line, -1, 1)) == '/') ? 'dir' : 'file',
                'path' => trim(substr($line, 2)),
            );
        }
        return $result;
    }
}

?>