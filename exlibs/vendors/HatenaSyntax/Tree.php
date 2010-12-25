<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Tree
{
    /**
     * array('level' => ?, 'value' => ?)の配列を渡す
     *
     * @param array $arr
     */
    static function make(Array $arr)
    {
        return new HatenaSyntax_Tree_Root(self::makeNodeArray($arr));
    } 
    
    static protected function makeNodeArray(Array $arr)
    {
        $i = 0;
        $len = count($arr);
        $tree_arr = array();
        $min_level = self::fetchMinLevel($arr);
        while ($i < $len) {
            list($tree_arr[], $i) = self::makeNode($arr, $i, $min_level);
        }
        return $tree_arr;
    }
    
    static protected function makeNode(Array $arr, $i, $min_level)
    {
        $children = array();
        $len = count($arr);
        if ($min_level < $arr[$i]['level']) {
            // Node
            for (; $i < $len && $min_level < $arr[$i]['level']; $i++) {
                $children[] = $arr[$i];
            }
            return array(new HatenaSyntax_Tree_Node(self::makeNodeArray($children)), $i);
        }
        else {
            // NodeかLeaf
            $value = $arr[$i]['value'];
            $i++;
            for (; $i < $len && $min_level < $arr[$i]['level']; $i++) {
                $children[] = $arr[$i];
            }
            $node = $children ? new HatenaSyntax_Tree_Node(self::makeNodeArray($children), $value) : 
                                new HatenaSyntax_Tree_Leaf($value);
            return array($node, $i);
        }
    }
    
    static protected function fetchMinLevel(Array $arr)
    {
        foreach ($arr as $elt) {
            if (!isset($level) || $level > $elt['level']) {
                $level = $elt['level'];
            }
        }
        return $level;
    }
}