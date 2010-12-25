<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Util
{
    static function concat(Array $arr)
    {
        $ret = array();
        foreach ($arr as $elt) foreach($elt as $val) $ret[] = $val;
        return $ret;
    }
    
    static function count(Array $result)
    {
        return count($result);
    }
    
    static function drop($result)
    {
        return null;
    }
    
    static function cons($result)
    {
        if (!is_null($result[0])) array_unshift($result[1], $result[0]);
        return $result[1];
    }
    
    static function flatten(Array $result)
    {
        $ret = array();
        foreach ($result as $elt) {
            if (is_array($elt)) $ret = array_merge($ret, self::flatten($elt));
            else $ret[] = $elt;
        }
        return $ret;
    }
    
    static function at($key, $result)
    {
        return $result[$key];
    }
    
    static function create($klass, $result)
    {
        return new $klass($result);
    }
    
    static function join($glue, Array $result)
    {
        $result = self::flatten($result);
        return join($glue, $result);
    }
    
    static function tail(Array $result)
    {
        return end($result);
    }
}