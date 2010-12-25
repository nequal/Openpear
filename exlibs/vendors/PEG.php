<?php
/**
 * このクラスは、静的メソッドから様々なパーサやコンテキスト等を生成するのに使われる。
 * 
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

include_once dirname(__FILE__) . '/PEG/IContext.php';
include_once dirname(__FILE__) . '/PEG/IParser.php';

include_once dirname(__FILE__) . '/PEG/Action.php';
include_once dirname(__FILE__) . '/PEG/And.php';
include_once dirname(__FILE__) . '/PEG/Anything.php';
include_once dirname(__FILE__) . '/PEG/ArrayContext.php';
include_once dirname(__FILE__) . '/PEG/CallbackAction.php';
include_once dirname(__FILE__) . '/PEG/Cache.php';
include_once dirname(__FILE__) . '/PEG/Char.php';
include_once dirname(__FILE__) . '/PEG/Choice.php';
include_once dirname(__FILE__) . '/PEG/Curry.php';
include_once dirname(__FILE__) . '/PEG/EOS.php';
include_once dirname(__FILE__) . '/PEG/Failure.php';
include_once dirname(__FILE__) . '/PEG/Lookahead.php';
include_once dirname(__FILE__) . '/PEG/Many.php';
include_once dirname(__FILE__) . '/PEG/Memoize.php';
include_once dirname(__FILE__) . '/PEG/Not.php';
include_once dirname(__FILE__) . '/PEG/Optional.php';
include_once dirname(__FILE__) . '/PEG/Ref.php';
include_once dirname(__FILE__) . '/PEG/Sequence.php';
include_once dirname(__FILE__) . '/PEG/StringContext.php';
include_once dirname(__FILE__) . '/PEG/Token.php';
include_once dirname(__FILE__) . '/PEG/Util.php';
include_once dirname(__FILE__) . '/PEG/ErrorReporter.php';
include_once dirname(__FILE__) . '/PEG/InstantParser.php';
include_once dirname(__FILE__) . '/PEG/Delay.php';

class PEG
{
    protected static function asParser($val)
    {
        return $val instanceof PEG_IParser ?  $val : self::token($val);
    }
    
    protected static function asParserArray(Array $arr)
    {
        foreach ($arr as &$val) {
            $val = self::asParser($val);
        }

        return $arr;
    }
    
    /**
     * 引数に応じて適切なPEG_IContextインスタンスを生成する。
     * 
     * @param string|Array $str
     * @return PEG_IContext
     * @see PEG_IContext, PEG_StringContext, PEG_ArrayContext
     */
    static function context($val)
    {
        if (is_string($val)) {
            return new PEG_StringContext($val);
        }

        if (is_array($val)) {
            return new PEG_ArrayContext($val);
        }

        throw new InvalidArgumentException();
    }
    
    /**
     * PEG_CallbackActionインスタンスを生成する。
     * PEG::callbackAction($callback, PEG::seq($a, $b, $c)), PEG::callbackAction($callback, $a, $b, $c) は同等
     * 
     * @param callable $callback
     * @param ?
     * @return PEG_CallbackAction
     * @see PEG_CallbackAction
     */
    static function callbackAction($callback, $p)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_CallbackAction($callback, self::asParser($p));
    }

    /**
     * self::callbackActionのエイリアス
     *
     * @param callable 
     * @param ?
     * @return PEG_CallbackAction
     */
    static function hook($callback, $p)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_CallbackAction($callback, self::asParser($p));
    }

    /**
     * 渡されたコールバックからPEG_InstantParserを返す
     *
     * @param callable
     * @return PEG_InstantParser
     */
    static function parserOf($callback)
    {
        return new PEG_InstantParser($callback);
    }

    /**
     * パーサを遅延評価する。パーサの実行時に与えられたコールバックが呼ばれ、返されたパーサを使う。
     *
     * @param callable
     * @return PEG_Delay
     */
    static function delay($callback)
    {
        return new PEG_Delay($callback);
    }
    
    /**
     * PEG_Anythingインスタンスを得る。
     * このパーサはどのような文字でもパースに成功する
     * 
     * @return PEG_Anything
     * @see PEG_Anything
     */
    static function anything()
    {
        static $obj = null;
        return $obj ? $obj : $obj = new PEG_Anything;
    }
    
    /**
     * PEG_Choiceインスタンスを生成する。
     * このパーサは、パース時に与えられたパーサを順に試していき、初めに成功したパーサの結果をそのまま返す
     * 全てのパーサが失敗したならば、このパーサは失敗する。
     * 
     * @return PEG_Choice
     * @param ...
     * @see PEG_Choice
     */
    static function choice()
    {
        return new PEG_Choice(self::asParserArray(func_get_args()));
    }

    /**
     * エラーを記録するパーサを返す
     *
     * @param string
     * @return PEG_ErrorReporter
     */
    static function error($msg)
    {
        return new PEG_ErrorReporter($msg);
    }
    
    /**
     * PEG_EOSインスタンスを得る。
     * このパーサは、パース時に対象が終端に来た、
     * つまり$aContext->eos() === trueの時の
     * PEG_IContextインスタンスを与えられたときのみ成功する。
     * 
     * @return PEG_EOS
     */
    static function eos()
    {
        static $obj = null;
        return $obj ? $obj : $obj = new PEG_EOS;
    }
    
    /**
     * PEG_Notインスタンスを得る。
     * このパーサは、$pパーサが成功したならば失敗し、$pパーサが失敗したならば成功する。
     * PEG_Notパーサは文字列を消費しない
     * PEG::not(PEG::seq($a, $b, $c)), PEG::not($a, $c, $c) は同等
     * 
     * @param $p
     * @return PEG_Not
     */
    static function not($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_Not(self::asParser($p));
    }
    
    /**
     * 与えられたパーサが失敗した場合でもfalseを返すパーサを返す
     * 正規表現でいう"?"
     * PEG::optional(PEG::seq($a, $b, $c)), PEG::optional($a, $b, $c) は同等。
     * 
     * @param $p
     * @return PEG_Parser
     */
    static function optional($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_Optional(self::asParser($p));
    }
    
    
    /**
     * 複数のパーサを一つにまとめて、それらのパーサの結果の配列を返す
     * パーサが一つでも失敗した場合、このパーサも失敗する
     * 与えられたパーサの結果がnullだった場合、結果の配列から除外される
     * 
     * @return PEG_Sequence
     */
    static function seq()
    {
        return new PEG_Sequence(self::asParserArray(func_get_args()));
    }
    
    /**
     * 与えられたパーサを失敗するまで繰り返し、結果の配列を返すパーサを得る
     * 与えられたパーサの結果がnullだった場合、結果の配列から除外される
     * PEG::many(PEG::seq($a, $b, $c)), PEG::many($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_Many
     */
    static function many($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_Many(self::asParser($p));
    }
    
    /**
     * 与えられたパーサを失敗するまで繰り返し、配列を返すパーサを得る
     * パーサが一度も成功しない場合は失敗する
     * 与えられたパーサの結果がnullだった場合、結果の配列から除外される
     * PEG::many1(PEG::seq($a, $b, $c)), PEG::many1($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_Many1
     */
    static function many1($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::callbackAction(array('PEG_Util', 'cons'), self::seq($p, self::many($p)));
    }
    
    /**
     * 与えられた引数とマッチするパーサを得る。
     * PEG_Tokenパーサの処理はPEG_IContext::token()に移譲される。
     * 
     * @param ...
     * @return PEG_Token
     */
    static function token()
    {
        $args = func_get_args();
        return new PEG_Token($args);
    }

    /**
     * 
     * @return PEG_And
     */
    static function andalso()
    {
        return new PEG_And(self::asParserArray(func_get_args()));
    }

    /**
     * 与えたリファレンスをパース時にパーサとして実行するパーサを得る
     * 
     * @return PEG_Ref
     */
    static function ref(&$parser)
    {
        return new PEG_Ref($parser);
    }

    /**
     * 与えた文字列に含まれる文字にヒットするパーサを得る
     * 
     * @param string $str
     * @return PEG_Char
     */
    static function char($str, $except = false)
    {
        return new PEG_Char($str, $except);
    }

    /**
     * 数字にヒットするパーサを得る
     * 
     * @return PEG_Char
     */
    static function digit()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::char('0123456789');
    }
    
    /**
     * 改行にヒットするパーサを得る
     * 
     * @return PEG_Choice
     */
    static function newLine()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::choice(self::token("\r\n"), self::char("\r\n"));
    }
    
    /**
     * 行の終わりにヒットするパーサを返す
     * 
     * @return PEG_Choice
     */
    static function lineEnd()
    {
        static $p = null;
        return $p ? $p : $p = self::choice(self::newLine(), self::eos());
    }
    
    /**
     * 行にヒットするパーサを返す
     * 
     * @return PEG_IParser
     */
    static function line()
    {
        static $p = null;
        return $p ? $p : $p = self::join(self::seq(self::many(self::second(self::not(self::newLine()), self::anything())), 
                                                   self::optional(self::newLine())));
    }
    
    /**
     * アルファベットの大文字にヒットするパーサを得る
     * 
     * @return PEG_Char
     */
    static function upper()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::char('ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }
    
    /**
     * アルファベットの小文字にヒットするパーサを得る
     * 
     * @return PEG_Char
     */
    static function lower()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::char('abcdefghijklmnopqrstuvwxyz');
    }
    
    /**
     * アルファベットにヒットするパーサを得る
     * 
     * @return PEG_Choice
     */
    static function alphabet()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::choice(self::lower(), self::upper());
    }

    /**
     * 
     * 
     * @param $key
     * @param $p
     * @return PEG_At
     */
    static function at($key, $p)
    {
        $curry = PEG_Curry::make(array('PEG_Util', 'at'), $key);
        return self::callbackAction($curry, $p);
    }

    /**
     * 与えられたパーサが何か値を返したとき、その値の最初の要素を返すパーサを得る
     * PEG::first(PEG::seq($a, $b, $c)), PEG::first($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_At
     */
    static function first($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::at(0, self::asParser($p));
    }

    /**
     * 与えられたパーサが何か値を返したとき、その値の二番目の要素を返すパーサを得る
     * PEG::second(PEG::seq($a, $b, $c)), PEG::second($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_At
     */
    static function second($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::at(1, self::asParser($p));
    }

    /**
     * 与えられたパーサが何か値を返したとき、その値の三番目の要素を返すパーサを得る
     * PEG::third(PEG::seq($a, $b, $c)), PEG::third($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_At
     */
    static function third($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::at(2, self::asParser($p));
    }

    /**
     * $start, $body, $endの三つのパーサを一つにまとめて、$bodyの返す値のみを返すパーサを得る
     * 
     * @param $start
     * @param $body
     * @param $end
     * @return PEG_At
     */
    static function pack($start, $body, $end)
    {
        return self::second(self::seq(self::asParser($start), self::asParser($body), self::asParser($end)));
    }

    /**
     * 与えられたパーサが返す配列を平らにするパーサを得る
     * PEG::flatten(PEG::seq($a, $b, $c)), PEG::flatten($a, $b, $c) と同等
     * 
     * @param $p
     */
    static function flatten($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::callbackAction(array('PEG_Util', 'flatten'), $p);
    }


    /**
     * 与えられたパーサがパース時に何を返そうともnullを返すパーサを得る
     * PEG::seq, PEG::many, PEG::many1の引数の一部に使うと、自動的にパースの結果から除外される
     * PEG::drop(PEG::seq($a, $b, $c), PEG::drop($a, $b, $c) は同等
     * 
     * @param $p
     * @return PEG_Drop 
     */
    static function drop($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::callbackAction(array('PEG_Util', 'drop'), $p);
    }

    /**
     * PEG::create('Klass', PEG::seq($a, $b, $c)), PEG::create('Klass', $a, $b, $c) は同等
     * 
     * @param string $klass
     * @param $p
     */
    static function create($klass, $p)
    {
        if (func_num_args() > 2) {
            $args = func_get_args();
            array_shift($args);
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        $curry = PEG_Curry::make(array('PEG_Util', 'create'), $klass);
        return self::callbackAction($curry, $p);
    }
    
    /**
     * 与えれたパーサがパース時に配列を返すとして、その配列をjoinして返すパーサを得る
     * 
     * @param $p
     * @param string $glue
     */
    static function join($p, $glue = '')
    {
        $curry = PEG_Curry::make(array('PEG_Util', 'join'), $glue);
        return self::callbackAction($curry, $p);
    }

    /**
     * 与えられたパーサがパース時に何か返す時、その値をcount()した値を返すパーサを得る
     * 
     * @param $p
     * @return PEG_CallbackAction
     */
    static function count($p)
    {
        return self::callbackAction(array('PEG_Util', 'count'), $p);
    }
    
    /**
     * 
     *
     * @param $item
     * @param $glue
     * @return PEG_CallbackAction
     */
    static function listof($item, $glue)
    {
        $parser = self::seq($item, self::many(self::second($glue, $item)));
        return self::callbackAction(array('PEG_Util', 'cons'), $parser);
    }

    /**
     * 半角空白かタブにヒットするパーサを得る
     *
     * @return PEG_Char
     */
    static function blank()
    {
        static $obj = null;
        return $obj ? $obj : $obj = self::char(" \t");
    }

    /**
     * 渡されたパーサがパース時に返す値の最後の値を返すパーサを得る
     * PEG::tail(PEG::seq($a, $b, $c)), PEG::tail($a, $b, $c) は同等
     *
     * @param unknown_type $p
     * @return PEG_IParser
     */
    static function tail($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return self::callbackAction(array('PEG_Util', 'tail'), self::asParser($p));
    }
    
    /**
     * 与えられたパーサを先読みパーサにする
     * PEG::lookaheadの代わりにこれを使う
     * PEG::amp(PEG::seq($a, $b, $c), PEG::amp($a, $b, $c) は同等
     *
     * @param $p
     * @return PEG_Lookahead
     */
    static function amp($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_Lookahead(self::asParser($p));
    }
    
    /**
     * PEG::subtract($a, $b, $c), PEG::tail(PEG::not($b), PEG::not($c), $a) は同等
     *
     * @param unknown_type $p
     * @return unknown
     */
    static function subtract($p)
    {
        $args = func_get_args();
        array_shift($args);
        foreach ($args as &$elt) {
            $elt = self::not(self::asParser($elt));
        }
        $args[] = self::asParser($p);
        return call_user_func_array(array('PEG', 'tail'), self::asParserArray($args));
    }
    
    /**
     * PEG_Failureインスタンスを返す
     *
     * @return PEG_Failure
     */
    static function failure()
    {
        return PEG_Failure::it();
    }
    
    /**
     * パーサをメモ化する
     * PEG::memo(PEG::seq($a, $b, $c)), PEG::memo($a, $b, $c) は同等
     *
     * @param $p
     * @return PEG_Memoize
     */
    static function memo($p)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $p = new PEG_Sequence(self::asParserArray($args));
        }
        return new PEG_Memoize(self::asParser($p));
    }

    /**
     * パーサが最初にヒットした時に返した値を返す
     *
     * @param PEG_IParser $parser
     * @param $subject
     * @return unknown
     */
    static function match(PEG_IParser $parser, $subject)
    {
        return self::_match($parser, self::context($subject));
    }
    
    static function _match(PEG_IParser $parser, PEG_IContext $context, $need_matching_start = false)
    {
        while(!$context->eos()) {
            $start = $context->tell();
            $result = $parser->parse($context);
            $end = $context->tell();
            if ($result instanceof PEG_Failure) {
                $context->seek($start + 1);
            }
            else {
                return $need_matching_start ? array($result, $start) : $result;
            }
        }
        return $need_matching_start ? array(self::failure(), null) : self::failure();
    }
    
    /**
     * パーサがヒットした時の値を全て返す
     * 
     * @param PEG_IParser
     * @param string
     * @return array
     */
    static function matchAll(PEG_IParser $parser, $subject)
    {
        $context = self::context($subject);
        $matches = array();
        while (!$context->eos()) {
            $result = self::_match($parser, $context);
            if (!$result instanceof PEG_Failure) {
                $matches[] = $result;
            }
        }
        
        return $matches;
    }
}
