<?php
class OpenpearTemplf
{
    final static public function str($a){
        return str($a);
    }
    final static public function gravatar($mail, $size=16){
        return sprintf('http://www.gravatar.com/avatar/%s?s=%d', md5($mail), $size);
    }
    final static public function tlicon($type){
        switch($type){
            case 'release':
                return Template::current_media_url(). '/images/global-icon-star.png';
            case 'changeset':
                return Template::current_media_url(). '/images/global-icon-checked.png';
            case 'user_activities':
                return Template::current_media_url(). '/images/global-icon-user.png';
            case 'package_setting':
                return Template::current_media_url(). '/images/global-icon-gear.png';
            case 'favorite':
                return Template::current_media_url(). '/images/global-icon-star.png';
        }
    }
    final static public function tlalt($type){
        switch($type){
            case 'release':
                return 'Release:';
            case 'changeset':
                return 'Changeset:';
            case 'user_activities':
                return 'Setting:';
            case 'package_setting':
                return 'Setting:';
            case 'favorite':
                return 'Fav:';
        }
    }
    
    final static public function count(array $array){
        return count($array);
    }
    final static public function hash($key='rand'){
        $key = ($key === 'rand')? mt_rand(0, 99999): $key;
        return sha1(md5($key));
    }
    final static public function d($v){
        Log::info('########## debug ##########');
        Log::d($v);
    }
}
