<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Renderer
{
    protected 
        $config, 
        $footnote, 
        $fncount, 
        $root, 
        $treeRenderer, 
        $headerCount;
    
    function __construct(Array $config = array())
    {
        $this->config = $config + array(
            'headerlevel'        => 1,
            'htmlescape'         => true,
            'id'                 => uniqid('sec'),
            'sectionclass'       => 'section',
            'footnoteclass'      => 'footnote',
            'keywordlinkhandler' => array($this, 'keywordLinkHandler'),
            'superprehandler'    => array($this, 'superPreHandler'),
            'linktitlehandler'   => array($this, 'linkTitleHandler')
        );
        
        $this->treeRenderer = new HatenaSyntax_TreeRenderer(
            array($this, 'listItemCallback'), 
            array($this, 'isOrderedCallback'));
    }

    static function linkTitleHandler($url)
    {
        return $url;
    }
    
    static function superPreHandler($type, $lines)
    {
        $body = join(PHP_EOL, array_map(array('HatenaSyntax_Renderer', 'escape'), $lines));
        return '<pre class="superpre">' . PHP_EOL . $body . '</pre>';
    }
    
    static function keywordLinkHandler($path)
    {
        return './' . $path;
    }
    
    function listItemCallback(Array $data)
    {
        list(, $lineSegment) = $data;
        return $this->renderLineSegment($lineSegment);
    }
    
    function isOrderedCallback(HatenaSyntax_Tree_INode $node)
    {
        $children = $node->getChildren();
        foreach ($children as $child) {
            if ($child->hasValue()) {
                $buf = $child->getValue();
                return $buf[0] === '+';
            }
        }
        return false;
    }
    
    function render(HatenaSyntax_Node $rootnode)
    {
        if ($rootnode->getType() !== 'root') throw new InvalidArgumentException();
        
        $this->footnote = '';
        $this->fncount = 0;
        $this->root = $rootnode;
        $this->headerCount = 0;

        $ret = $this->renderNode($rootnode);
        $ret = '<div class="' . $this->config['sectionclass'] . '">' . PHP_EOL . $ret . PHP_EOL . '</div>' . PHP_EOL;
        if ($this->fncount > 0) {
            $ret .= PHP_EOL . PHP_EOL . '<div class="' . $this->config['footnoteclass'] . '">' . 
                    PHP_EOL . $this->footnote .  '</div>';
        }
        
        return $ret;
    }

    function renderTitle(HatenaSyntax_Node $root)
    {
        if ($root->getType() !== 'root') {
            throw new InvalidArgumentException();
        }

        $this->footnote = '';
        $this->fncount = 0;
        $this->root = $root;
        $this->headerCount = 0;

        $nodes = $root->getData();

        if (isset($nodes[0]) && $nodes[0]->isTopHeader()) {
            return strip_tags($this->renderLineSegment($nodes[0]->at('body')));
        }
        return '';
    }

    protected function renderSeparator()
    {
        return '<div class="separator"></div>' . PHP_EOL;
    }
    
    protected function renderTableOfContents()
    {
        $tocRenderer = new HatenaSyntax_TOCRenderer();
        return $tocRenderer->render($this->root, $this->config['id']);
    }
    
    protected function renderNode(HatenaSyntax_Node $node)
    {
        $ret = $this->{'render' . $node->getType()}($node->getData());
        return $ret;
    }
    
    protected function renderRoot(Array $arr)
    {
        foreach ($arr as &$elt) $elt = $this->renderNode($elt);
        return join(PHP_EOL, $arr);
    }
    
    protected function renderHeader(Array $data)
    {
        $level = $data['level'] + $this->config['headerlevel'];   
        $name  = 'hs_' . md5($this->config['id']) . '_header_' . $this->headerCount++;

        return "<h{$level} id=\"{$name}\">" . $this->renderLineSegment($data['body']) . "</h{$level}>";
    }

    protected function renderNoParagraph(Array $data)
    {
        $ret = '';
        $ret .= "<{$data['tag']}";
        foreach ($data['attr'] as $name => $value) {
            $ret .= " {$name}=\"{$value}\"";
        }
        $ret .= ">" . PHP_EOL;

        $ret .= $this->renderLineSegment($data['body']);

        $ret .= PHP_EOL . "</{$data['tag']}>" . PHP_EOL;

        return $ret;
    }
    
    protected function renderLineSegment(Array $data)
    {
        $data = self::normalize($data);
        foreach ($data as &$elt) {
            $elt = !$elt instanceof HatenaSyntax_Node 
                ? ($this->config['htmlescape'] ? $this->escape($elt) : $elt) 
                : $this->renderNode($elt);
        }
        return join('', $data);
    }
    
    protected function renderFootnote(Array $data)
    {
        $this->fncount++;

        $id = md5($this->config['id']);
        $n = $this->fncount;

        $body = $this->renderLineSegment($data);
        $title = strip_tags($body);

        if (!$this->config['htmlescape']) {
            $title = $this->escape($title);
        }
        
        $fnname = sprintf('hs_%s_footnote_%d', $id, $n);
        $fnlinkname = sprintf('hs_%s_footnotelink_%d', $id, $n);

        $this->footnote .= sprintf('<p id="%s"><a href="#%s">*%d</a>: %s</p>' . PHP_EOL, $fnname, $fnlinkname, $n, $body);

        return sprintf('(<a href="#%s" id="%s" title="%s">*%d</a>)', $fnname, $fnlinkname, $title, $n);
    }

    protected function renderInlineTag(Array $data)
    {
        return 
            "<{$data['name']}>" 
            . $this->renderLineSegment($data['body']) 
            . "</{$data['name']}>";
    }
    
    protected function renderHttpLink(Array $data)
    {
        list($href, $title) = array($data['href'], $data['title']);

        if ($title === '') {
            $title = call_user_func($this->config['linktitlehandler'], $href);
        }
        elseif ($title === false) {
            $title = $href;
        }

        return sprintf('<a href="%s">%s</a>', self::escape($href), self::escape($title));
    }
    
    protected function renderImageLink($url)
    {
        $url = self::escape($url);
        return '<a href="' . $url . '"><img src="' . $url . '" /></a>';
    }
    
    protected function renderKeywordLink($path)
    {
        $href = call_user_func($this->config['keywordlinkhandler'], $path);
        return '<a href="' . self::escape($href) . '">' . self::escape($path) . '</a>';
    }
    
    protected function renderDefinitionList(Array $data)
    {
        foreach ($data as &$elt) $elt = $this->renderDefinition($elt);
        return join(PHP_EOL, array('<dl>', join(PHP_EOL, $data), '</dl>'));
    }
    
    protected function renderDefinition(Array $data)
    {
        list($dt, $dd) = $data;
        $ret = array();
        if ($dt) $ret[] = '<dt>' . $this->renderLineSegment($dt) . '</dt>';
        $ret[] = '<dd>' . $this->renderLineSegment($dd) . '</dd>';
        return join(PHP_EOL, $ret);
    }
    
    protected function renderPre(Array $data)
    {
        $ret = array();
        $ret[] = '<pre>';
        foreach ($data as &$elt) $elt = $this->renderLineSegment($elt);
        $ret[] = join(PHP_EOL, $data) . '</pre>';
        return join(PHP_EOL, $ret);
    }
    
    protected function renderSuperPre(Array $data)
    {
        $ret = array();
        list($type, $lines) = array($data['type'], $data['body']);
        $ret[] = call_user_func($this->config['superprehandler'], $type, $lines);
        return join(PHP_EOL, $ret);
    }
    
    protected function renderTable(Array $data)
    {
        $ret = array();
        $ret[] = '<table>';
        foreach ($data as $tr) {
            $ret[] = '<tr>';
            foreach ($tr as $td) $ret[] = $this->renderTableCell($td[0], $td[1]);
            $ret[] = '</tr>';
        }
        $ret[] = '</table>';
        return join(PHP_EOL, $ret);
    }
    
    protected function renderTableCell($header, $segment)
    {
        $tag = $header ? 'th' : 'td'; 
        $ret = "<{$tag}>" . $this->renderLineSegment($segment) . "</{$tag}>";
        return $ret;
    }
    
    protected function renderBlockQuote(Array $arr)
    {
        $ret = array();
        $ret[] = '<blockquote>';
        foreach ($arr['body'] as $elt) $ret[] = $this->renderNode($elt);
        if ($arr['url']) {
            $ret[] = '<cite>' . $this->renderHttpLink($arr['url']->getData()) . '</cite>';
        }
        $ret[] = '</blockquote>';
        return join(PHP_EOL, $ret);
    }
    
    protected function renderParagraph(Array $data)
    {
        return '<p>' . $this->renderLineSegment($data) . '</p>';
    }
    
    protected function renderEmptyParagraph($data)
    {
        return str_repeat('<br />' . PHP_EOL, max($data - 1, 0));
    }
    
    protected function renderList(HatenaSyntax_Tree_Root $root)
    {
        return $this->treeRenderer->render($root);
    }
    
    
    protected static function escape($str)
    {
        if (!is_string($str)) {
            debug_print_backtrace();
            return;
        }
        return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
    }

    /**
     * @param Array  
     * @return Array
     */
    protected static function normalize(Array $arr)
    {
        $ret = array();
        
        while ($arr) {
            list($elt, $arr) = self::segment($arr);
            $ret[] = $elt;
        }

        return $ret;
    }

    /**
     * @param Array
     * @return Array array($elt, $rest)
     */
    static function segment(Array $arr)
    {
        $first = array_shift($arr);

        if (!is_string($first)) {
            return array($first, $arr);
        }

        $str = $first;
        while ($arr) {
            if (is_string($arr[0])) {
                $str .= array_shift($arr);
            }
            else {
                return array($str, $arr);
            }
        }
        return array($str, array());
    }
}
