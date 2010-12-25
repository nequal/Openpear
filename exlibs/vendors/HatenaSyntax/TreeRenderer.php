<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_TreeRenderer
{
    protected $valueCallback, $isOrderedCallback;
    
    function __construct($valueCallback, $isOrderedCallback = false)
    {
        $this->valueCallback = $valueCallback;
        $this->isOrderedCallback = $isOrderedCallback ? $isOrderedCallback : array($this, 'isOrderedDefaultCallback');
    }
    
    function isOrderedDefaultCallback($node)
    {
        return true;
    }
    
    protected function renderValue($value)
    {
        return call_user_func($this->valueCallback, $value);
    }
    
    protected function isOrdered($node)
    {
        return call_user_func($this->isOrderedCallback, $node);
    }
    
    protected function listOpenTag($bool)
    {
        return ($bool ? '<ol>' : '<ul>') . PHP_EOL;
    }
    
    protected function listCloseTag($bool)
    {
        return ($bool ? '</ol>' : '</ul>') . PHP_EOL;
    }
    
    function render(HatenaSyntax_Tree_Root $root)
    {
        $ordered = $this->isOrdered($root);
        $ret = $this->listOpenTag($ordered);
        foreach ($root->getChildren() as $child) {
            $ret .= $this->_render($child);
        }
        $ret .= $this->listCloseTag($ordered);
        return $ret;
    }
    
    protected function _render($node)
    {
        return $this->{'render' . $node->getType()}($node);
    }
    
    protected function renderNode($node)
    {
        $ret = '<li>' . PHP_EOL;
        if ($node->hasValue()) $ret .= $this->renderValue($node->getValue());
        $ordered = $this->isOrdered($node);
        $ret .= PHP_EOL . $this->listOpenTag($ordered);
        foreach ($node->getChildren() as $child) {
            $ret .= $this->_render($child);
        }
        $ret .= $this->listCloseTag($ordered);
        $ret .= '</li>' . PHP_EOL;
        return $ret;
    }
    
    protected function renderLeaf($node)
    {
        return '<li>' . $this->renderValue($node->getValue()) . '</li>' . PHP_EOL;
    }
}
