<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionListException');

class SubversionList extends Subversion
{
    protected $_command_ = 'list';
    
    protected function __exec__(){
        $this->options('xml');
        if(Tag::setof($tag, parent::__exec__(), 'list')){
            $result = array();
            foreach($tag->in('entry') as $t) $result[] = $t->hash();
            return $result;
        }
        throw new SubversionListException();
    }
}
