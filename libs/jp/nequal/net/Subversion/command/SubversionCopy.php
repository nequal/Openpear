<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionCopyException');

class SubversionCopy extends Subversion
{
    protected $_command_ = 'copy';
    protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if(strpos($ret, 'Committed revision') !== false){
            return true;
        }
        throw new SubversionCopyException();
    }
}
