<?php
module('command.SubversionCommand');
module('exception.SubversionCopyException');

class SubversionCopy extends SubversionCommand
{
    protected $_command_ = 'copy';
    static protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if(strpos($ret, 'Committed revision') !== false){
            return true;
        }
        throw new SubversionCopyException();
    }
}
