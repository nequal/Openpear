<?php
module('command.SubversionCommand');
module('exception.SubversionLogException');

class SubversionLog extends SubversionCommand
{
    protected $_command_ = 'log';
    protected $raw = false;
    static protected $__raw__ = 'type=boolean';
    
    protected function __exec__(){
        if($this->is_raw()){
            return parent::__exec__();
        }
        $this->options('xml');
        $result = array();
        if(Tag::setof($tag, parent::__exec__()->stdout(), 'log')){
            foreach($tag->in('logentry') as $logentry) $result[] = $logentry->hash();
            return $result;
        }
        throw new SubversionLogException();
    }
}
