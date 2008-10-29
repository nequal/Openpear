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
        return is_string($str) ? strip_tags($str) : '';
    }
}