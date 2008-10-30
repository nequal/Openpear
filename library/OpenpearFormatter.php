<?php
Rhaco::import('HatenaSyntax');

class OpenpearFormatter
{
    /**
     * 
     */
    function d($string, $id='project_description'){
        if(!is_string($string)){
            return '';
        }
        $options = array(
            'headlevel' => 4,
            'htmlescape' => true,
            'id' => $id,
        );
        $hatena = new HatenaSyntax($options);
        return $hatena->parse($string);
    }
    function st($str){
        return is_string($str) ? trim(strip_tags($str)) : '';
    }
    function dn($str){
        $dn = dirname($str);
        return ($dn == '.') ? '' : $dn;
    }
    function pp($str){
        list(, $n) = explode('/', $str);
        return $n;
    }
}