<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

include_once dirname(__FILE__) . '/INode.php';

class HatenaSyntax_Tree_Root implements HatenaSyntax_Tree_INode
{
    protected $children;
    
    function __construct(Array $children)
    {
        $this->children = $children;
    }
    
    function hasValue()
    {
        return false;
    }
    
    function getValue()
    {
        return null;
    }
    
    function hasChildren()
    {
        return true;
    }
    
    function getChildren()
    {
        return $this->children;
    }
    
    function getType()
    {
        return 'root';
    }
}