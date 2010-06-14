<?php
/**
 * PEAR 連携用初期化
 **/
require_once dirname(__DIR__). '/__settings__.php';

/**
 * エラーをもみ消す
 * PEAR のバカ！
 **/
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (
        $errstr == 'Assigning the return value of new by reference is deprecated'
        || preg_match('/^Non-static method/', $errstr)
        || preg_match('/is deprecated$/', $errstr)
        || preg_match('/^Use of undefined constant/', $errstr)
        || preg_match('/^Declaration of/', $errstr)
    ) {
        return true;
    }
    switch ($errno) {
        case E_USER_ERROR:
        case E_USER_WARNING;
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        default:
            Log::debug(new ErrorException($errstr, 0, $errno, $errfile, $errline));
    }
    return true;
});

