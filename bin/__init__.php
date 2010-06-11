<?php
/**
 * PEAR 連携用初期化
 **/
require_once dirname(dirname(__FILE__)). '/__settings__.php';

/**
 * エラーをもみ消す
 * PEAR のバカ！
 **/
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    switch ($errno) {
        case E_USER_ERROR:
        case E_USER_WARNING;
            throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
        default:
            Log::info(new ErrorException($errstr, 0, $errno, $errfile, $errline));
    }
    return true;
});

