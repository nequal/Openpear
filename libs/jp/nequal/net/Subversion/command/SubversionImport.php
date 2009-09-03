<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionImportException');

class SubversionImport extends Subversion
{
    protected $_command_ = 'import';
    protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if(strpos($ret, 'Committed revision') !== false){
            return true;
        }
        throw new SubversionImportException();
    }
}
