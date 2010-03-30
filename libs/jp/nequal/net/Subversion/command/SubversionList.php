<?php
module('command.SubversionCommand');
module('exception.SubversionListException');

class SubversionList extends SubversionCommand
{
    protected $_command_ = 'list';
    
    protected function __exec__(){
        $this->options('xml');
        if(Tag::setof($tag, parent::__exec__()->stdout(), 'list')){
            $result = array();
            foreach($tag->in('entry') as $t) $result[] = $t->hash();
            return $result;
        }
        throw new SubversionListException();
    }
}
