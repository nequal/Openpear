<?php
module('command.SubversionCommand');
module('exception.SubversionImportException');

class SubversionImport extends SubversionCommand
{
    protected $_command_ = 'import';
    static protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if(strpos($ret, 'Committed revision') !== false){
            return true;
        }
        throw new SubversionImportException();
    }
}
