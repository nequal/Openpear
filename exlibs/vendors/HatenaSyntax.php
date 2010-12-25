<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

include_once 'PEG.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Node.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Regex.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Locator.php';
include_once dirname(__FILE__) . '/HatenaSyntax/NodeCreater.php';
include_once dirname(__FILE__) . '/HatenaSyntax/InlineTag.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Renderer.php';
include_once dirname(__FILE__) . '/HatenaSyntax/TOCRenderer.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Util.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Header.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Block.php';
include_once dirname(__FILE__) . '/HatenaSyntax/LineElement.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Quote.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Table.php';
include_once dirname(__FILE__) . '/HatenaSyntax/DefinitionList.php';
include_once dirname(__FILE__) . '/HatenaSyntax/List.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Paragraph.php';
include_once dirname(__FILE__) . '/HatenaSyntax/NoParagraph.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Pre.php';
include_once dirname(__FILE__) . '/HatenaSyntax/SuperPre.php';
include_once dirname(__FILE__) . '/HatenaSyntax/TreeRenderer.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Tree.php';
include_once dirname(__FILE__) . '/HatenaSyntax/CommentRemover.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Tree/INode.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Tree/Node.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Tree/Root.php';
include_once dirname(__FILE__) . '/HatenaSyntax/Tree/Leaf.php';

class HatenaSyntax
{
    /**
     * 文字列をパースしてHatenaSyntax_Nodeインスタンスからなる構文木を返す。
     *
     * @param string
     * @return HatenaSyntax_Node
     */
    static function parse($str)
    {
        return HatenaSyntax_Locator::it()->parser->parse(self::context($str));
    }

    /**
     * 文字列をパースしてHatenaSyntax_Nodeインスタンスからなる構文木の配列を返す
     *
     * @param string
     * @return Array
     */
    static function parseAsSections($str)
    {
        $blocks = self::parse($str)->getData();

        // 先頭にある空のパラグラフを削る
        foreach ($blocks as $i => $block) {
            if ($block->getType() === 'emptyparagraph') {
                unset($blocks[$i]);
            }
            else {
                break;
            }
        }
        $blocks = array_values($blocks);

        // セクションごとにブロック要素をまとめる
        $sections = array();
        $len = count($blocks);
        $blocks = array_reverse($blocks);
        for ($i = 0; $i < $len; $i++) {
            $section = array();
            for (;$i < $len; $i++) {
                $section[] = $blocks[$i];
                if ($blocks[$i]->isTopHeader()) {
                    break;
                }
            }
            $sections[] = array_reverse($section);
        }
        $sections = array_reverse($sections);

        // ブロック要素の配列をノードにする
        foreach ($sections as $i => $section) {
            $sections[$i] = new HatenaSyntax_Node('root', $section);
        }

        return $sections;
    }

    /**
     * 文字列をパースしてhtmlを返す。
     *
     * @param string
     * @param Array
     * @return string
     */
    static function render($str, Array $config = array())
    {
        $node = self::parse($str);
        $renderer = new HatenaSyntax_Renderer($config);
        return $renderer->render($node);
    }
    
    /**
     * @param string
     * @return PEG_IContext
     */
    static protected function context($str)
    {
        $str = str_replace(array("\r\n", "\r"), "\n", $str);
        $str = strpos('<!--', $str) === false ? $str : HatenaSyntax_CommentRemover::remove($str);

        return PEG::context(preg_split("/\n/", $str));
    }

    /**
     * HatenaSyntax_Nodeインスタンスからなる構文木をhtmlにして返す。
     *
     * @param HatenaSyntax_Node 
     * @param Array
     * @return string
     */
    static function renderNode(HatenaSyntax_Node $root, Array $config = array())
    {
        $renderer = new HatenaSyntax_Renderer($config);
        return $renderer->render($root);
    }

    /**
     * セクション名を取得する。
     * 見つからなかった場合は空の文字列を返す。
     *
     * @param HatenaSyntax_Node
     * @return string
     */
    static function getSectionName(HatenaSyntax_Node $root)
    {
        self::assertRootNode($root);

        list($block) = $root->getData() + array(false);

        return $block && $block->isTopHeader() 
            ? (string)$block->at('name', '')
            : '';
    }

    /**
     * セクションのタイトルを取得する。
     * 見つからなかった場合は空の文字列を返す。
     *
     * @param HatenaSyntax_Node
     * @param Array $config HatenaSyntax_Rendererに渡す設定
     * @return string
     */
    static function getSectionTitle(HatenaSyntax_Node $root, Array $config = array())
    {
        self::assertRootNode($root);

        $renderer = new HatenaSyntax_Renderer($config);
        return $renderer->renderTitle($root);
    }

    /**
     * セクションの先頭にトップのヘッダを持っているかを取得する
     *
     * @param HatenaSyntax_Node
     * @return bool
     */
    static function hasTopHeader(HatenaSyntax_Node $root)
    {
        self::assertRootNode($root);

        list($block) = $root->getData() + array(false);

        return $block && $block->isTopHeader();
    }

    /**
     * 続きを読む記法があるかどうかを取得する
     *
     * @param HatenaSyntax_Node
     * @return bool
     */
    static function hasSeparator(HatenaSyntax_Node $root)
    {
        self::assertRootNode($root);
        
        foreach ($root->getData() as $block) {
            if ($block->getType() === 'separator') {
                return true;
            }
        }
        return false;
    }

    /**
     * 続きを読む記法よりも後ろの部分を切り離した前半の章を取得する
     * 
     * @param HatenaSyntax_Node
     * @return HatenaSyntax_Node
     */
    static function separate(HatenaSyntax_Node $root)
    {
        self::assertRootNode($root);

        $blocks = array();

        foreach ($root->getData() as $block) {
            $blocks[] = $block;
            if ($block->getType() === 'separator') {
                break;
            }
        }

        return new HatenaSyntax_Node('root', $blocks);
    }

    static protected function assertRootNode(HatenaSyntax_Node $node)
    {
        if ($node->getType() !== 'root') {
            throw new InvalidArgumentException('this node must be root node');
        }
    }
}
