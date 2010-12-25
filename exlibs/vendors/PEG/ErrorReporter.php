<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

/**
 * エラーをコンテキストに記録して失敗するパーサ
 */
class PEG_ErrorReporter implements PEG_IParser
{
    function __construct($error)
    {
        $this->error = $error;
    }
    
    function parse(PEG_IContext $context)
    {
        $context->logError($this->error);
        return PEG::failure();
    }
}
