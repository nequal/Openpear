<?php
Rhaco::import("model.table.RepositoryLogTable");
Rhaco::import('SvnUtil');
/**
 * 
 */
class RepositoryLog extends RepositoryLogTable{
    function parseSvnlookChanged($str){
        $result = array();
        $lines = explode("\n", $str);
        foreach($lines as $line){
            $result[] = array(
                'status' => substr($line, 0, 2),
                'type' => (trim(substr($line, -1, 0)) == '/') ? 'dir' : 'file',
                'path' => trim(substr($line, 2)),
            );
        }
        return $result;
    }
}

?>