<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Cache
{
    protected $data = array();
    
    function save(PEG_IParser $parser, $start, $end, $val)
    {
        $this->data[$this->genkey($parser, $start)] = array($end, $val);
    }
    
    protected function genkey($parser, $start)
    {
        return spl_object_hash($parser) . ':' . $start;
    }
    
    function cache(PEG_IParser $parser, $start)
    {
        $key = $this->genkey($parser, $start);
        return isset($this->data[$key]) ? array(true, $this->data[$key]) : array(false, false);
    }
}