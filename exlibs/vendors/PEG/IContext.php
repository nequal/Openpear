<?php
/**
 * PEG_IParserが必要とするコンテキスト
 * 
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

interface PEG_IContext
{
    /**
     * 対象の現在の位置を得る。
     * 
     * @return int
     */
    function tell();
    
    /**
     * 対象の現在の位置を設定する。
     *
     * @param int $i
     */
    function seek($i);
    
    /**
     * 対象の一部を$i分返す。その際に$iだけ現在位置も変更する。
     *
     * @param int $i
     * @return ?
     */
    function read($i);
    
    /**
     * 対象の要素を一つ返す。その際に現在位置も変更する。
     *
     * @return ?
     */
    function readElement();
    
    /**
     * 読み込むべきものが無い場合trueを返す。
     *
     * @return bool
     */
    function eos();
    
    /**
     * コンテキストが持つ対象全体を返す
     * 実装クラスは例外を投げることでこれを拒否できる
     *
     * @return ?
     */
    function get();
    
    /**
     * このメソッドの実装にはPEG_Cacheクラスの使用を推奨する
     *
     * @param PEG_IParser 
     * @param int 
     * @param ? 
     */
    function save(PEG_IParser $parser, $start, $end, $val);
    
    /**
     * このメソッドの実装にはPEG_Cacheクラスの使用を推奨する
     * array(hit, array(end, val))を返す
     *
     * @param PEG_IParser
     * @param int
     * @return array
     */
    function cache(PEG_IParser $parser);

    /**
     * 与えられた引数にしたがって現在位置を変更し、マッチしたものを返す
     * もしくはPEG_Failureを返す
     *
     * @param Array
     * @return ?
     */
    function token(Array $args);

    /**
     * 現在位置と共にエラーを記録する
     *
     * @param string
     * @return null
     */
    function logError($error);

    /**
     * 一番深い位置にあるエラーを返す
     * 返すべきエラーがある場合は、位置とエラー内容の配列を返す
     * そうでないばあいはnullを返す
     *
     * @return array|null
     */
    function lastError();
}
