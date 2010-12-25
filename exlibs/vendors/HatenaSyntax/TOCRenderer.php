<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_TOCRenderer
{
    protected $headerCount;
    
    function render(HatenaSyntax_Node $rootnode, $id)
    {
        $treeroot = HatenaSyntax_Tree::make($this->filter($rootnode));
        $this->headerCount = 0;
        $this->id = $id;
        $renderer = new HatenaSyntax_TreeRenderer(array($this, 'renderHeader'));
        return '<div class="toc">' . $renderer->render($treeroot) . '</div>';
    }
    
    protected function escape($str)
    {
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }
    
    function renderHeader($node)
    {
        $count = $this->headerCount++;
        $buf = array();
        $data = $node->getData();
        foreach ($data['body'] as $leaf) {
            if (is_string($leaf)) {
                $buf[] = $leaf;
            }
            else {
                $buf [] = $this->{'render' . $leaf->getType()}($lead->getData());
            }
        }
        return '<a href="#hs_' . md5($this->id) . '_header_' . $count . '">' . $this->escape(join('', $buf)) . '</a>';
    }
    
    protected function renderFootnote($data)
    {
        return '';
    }
    
    function filter(HatenaSyntax_Node $rootnode)
    {
        if ($rootnode->getType() !== 'root') throw new Exception;
        $header_arr = $this->fetchHeader($rootnode);
        $ret = array();
        foreach ($header_arr as $header) {
            $buf = $header->getData();
            $ret[] = array('level' => $buf['level'], 'value' => $header);
        }
        return $ret;
    }
    
    protected function fetchHeader($node)
    {
        return $this->{'fetchHeaderIn' . $node->getType()}($node);
    }
    
    protected function fetchHeaderInRoot($node)
    {
        $buf = array();
        foreach ($node->getData() as $node) {
            $buf[] = $this->fetchHeader($node);
        }
        
        return $this->concat($buf);
    }
    
    protected function fetchHeaderInHeader($node)
    {
        return array($node);
    }
    
    protected function fetchHeaderInBlockQuote($node)
    {
        $buf = array();
        $data = $node->getData();
        foreach ($data['body'] as $node) {
            $buf[] = $this->fetchHeader($node);
        }
        return $this->concat($buf);
    }

    function __call($name, $args)
    {
        if (preg_match('#^fetchHeaderIn\w+$#i', $name)) return array();
        throw new Exception(sprintf('method(%s) not found', $name));
    }
    
    protected function concat(Array $target)
    {
        if (!$target) return array();
        $target = array_reverse($target);
        while (1 < count($target)) {
            array_push($target, array_merge(array_pop($target), array_pop($target)));
        }
        return $target[0];
    }
}
