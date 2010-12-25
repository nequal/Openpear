<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

/**
 * どのような要素にもヒットするパーサ
 *
 */
class PEG_Anything implements PEG_IParser
{
    function parse(PEG_IContext $context)
    {
        return $context->eos() ? PEG::failure() : $context->readElement();
    }
}
