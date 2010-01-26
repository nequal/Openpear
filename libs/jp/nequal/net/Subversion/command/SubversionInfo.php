<?php
module('command.SubversionCommand');
module('exception.SubversionInfoException');

class SubversionInfo extends SubversionCommand
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
