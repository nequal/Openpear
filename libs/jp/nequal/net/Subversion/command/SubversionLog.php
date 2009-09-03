<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionLogException');

class SubversionLog extends Subversion
{
    protected $_command_ = 'log';
    protected $raw = false;
    static protected $__raw__ = 'type=boolean';
    
    protected function __exec__(){
        if($this->isRaw()){
            return parent::__exec__();
        }
        $this->options('xml');
        $result = array();
        if(Tag::setof($tag, parent::__exec__(), 'log')){
            foreach($tag->in('logentry') as $logentry) $result[] = $logentry->hash();
            return $result;
        }
        throw new SubversionLogException();
    }
}
