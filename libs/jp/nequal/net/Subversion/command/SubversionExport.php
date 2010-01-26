<?php
module('command.SubversionCommand');
module('exception.SubversionExportException');

class SubversionExport extends SubversionCommand
{
    protected $_command_ = 'export';
    static protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if((strpos($ret, 'Export complete.') !== false || strpos($ret, 'Exported revision') !== false)){
            return true;
        }
        throw new SubversionExportException();
    }
}
