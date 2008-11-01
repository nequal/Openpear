<?php
Rhaco::import('lang.ArrayUtil');
Rhaco::import('tag.model.TemplateFormatter');
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
    function gravatar($mail, $opt=''){
        $option = array(
            's' => 16,
            'r' => 'R',
        );
        if(!empty($opt)){
            $params = ArrayUtil::dict($opt, array('d', 's', 'r'), false);
            $option = array_merge($option, $params);
        }
        if(!isset($option['d'])) $option['d'] = 'http://openpear.org/icons/?s='. $option['s'];

        $url = 'http://www.gravatar.com/avatar/'. md5($mail). '?'.
            TemplateFormatter::httpBuildQuery($option);
        return sprintf('<img src="%s" width="%s" height="%s" alt="gravatar" />',
            $url,
            $option['s'], $option['s']
        );
    }
    function svnState($state){
        switch($state){
            case 'A ':
                return 'added';
            case 'D ':
                return 'removed';
            case 'U ':
            case '_U':
            case 'UU':
                return 'modified';
        }
        return '';
    }
}