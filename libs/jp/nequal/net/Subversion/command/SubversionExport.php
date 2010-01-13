<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionExportException');

class SubversionExport extends Subversion
{
    protected $_command_ = 'export';
    protected $_lang_ = 'C';
    
    protected function __exec__(){
        $ret = parent::__exec__();
        if((strpos($ret, 'Export complete.') !== false || strpos($ret, 'Exported revision') !== false)){
            return true;
        }
        throw new SubversionExportException();
    }
}
