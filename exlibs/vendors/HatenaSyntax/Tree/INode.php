<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

interface HatenaSyntax_Tree_INode
{
    function hasValue();
    function getValue();
    function hasChildren();
    
    // HatenaSyntax_Tree_INodeの配列を返す
    function getChildren();
    
    function getType();
}