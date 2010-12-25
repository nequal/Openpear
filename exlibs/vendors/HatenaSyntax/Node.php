<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Node
{
    protected $type, $offset, $data, $contextHash;
    function __construct($type, $data = array(), $offset = null, $contextHash = null)
    {
        $this->type = $type;
        $this->data = $data;
        $this->offset = $offset;
        $this->contextHash = $contextHash;
    }

    function getContextHash()
    {
        return $this->contextHash;
    }

    function getOffset()
    {
        return $this->offset;
    }
    
    function getType()
    {
        return $this->type;
    }
    
    function getData()
    {
        return $this->data;
    }

    function at($name, $defaultVal = null)
    {
        return array_key_exists($name, $this->data) 
            ? $this->data[$name] 
            : $defaultVal;
    }

    function isTopHeader()
    {
        return $this->type === 'header' && $this->at('level') === 0;
    }
}
