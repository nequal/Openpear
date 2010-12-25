<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Char implements PEG_IParser
{
    protected $dict = array(), $except;

    /**
     * @param string
     * @param bool
     */
    function __construct($str, $except = false)
    {
        $this->except = $except;
        foreach (str_split($str) as $c) {
            $this->dict[$c] = true;
        }
    }

    function parse(PEG_IContext $context)
    {
        $char = $context->readElement();
        return $char !== false && ($this->except xor isset($this->dict[$char]))
            ? $char
            : PEG::failure();
    }
}
