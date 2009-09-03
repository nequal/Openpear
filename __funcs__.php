<?php
if(!function_exists('parse_ini_string')){
    function parse_ini_string($ini, $process_sections=false){
        $tmpfile = tempnam(sys_get_temp_dir(), 'INI');
        if(@file_put_contents($tmpfile, $ini) !== false){
            $r = parse_ini_file($tmpfile, $process_sections);
            @unlink($tmpfile);
            return $r;
        }
        return false;
    }
}
if(!function_exists('sys_get_temp_dir')){
    function sys_get_temp_dir(){
        if(ini_get('temp_dir')){
            return realpath(ini_get('temp_dir'));
        }
        if(isset($_SERVER['TEMP']) && !empty($_SERVER['TEMP'])){
            return realpath($_SERVER['TEMP']);
        }
        if(isset($_SERVER['TMP']) && !empty($_SERVER['TMP'])){
            return realpath($_SERVER['TMP']);
        }
        return substr(PHP_OS, 0, 3) == 'WIN'? 'C:\TEMP': '/tmp';
    }
}