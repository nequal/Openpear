<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Util
{
    static function normalizeList(Array $data)
    {
        return HatenaSyntax_Tree::make($data);
    }

    static function processListItem(Array $li)
    {
        $ret = array();
        $ret['level'] = count($li[0]) - 1;
        $ret['value'] = array(end($li[0]), $li[1]);
        
        return $ret;
    }
}
