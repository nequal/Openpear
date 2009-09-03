<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionInfoException');

class SubversionInfo extends Subversion
{
    protected $_command_ = 'info';
    
    protected function __before_exec__(){
        $this->options('xml');
    }
    protected function __after_exec__(&$ret){
        if(Tag::setof($tag, $ret, 'info')){
            foreach($tag->in('entry') as $entry){
                $ret = $entry->hash();
                return;
            }
        }
        throw new SubversionInfoException();
    }
}
