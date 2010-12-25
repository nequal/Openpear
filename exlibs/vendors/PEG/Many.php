<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Many implements PEG_IParser
{
    protected $parser;
    function __construct(PEG_IParser $p)
    {
        $this->parser = $p;
    }
    function parse(PEG_IContext $context)
    {
        $ret = array();
        do {
            $offset = $context->tell();
            $result = $this->parser->parse($context);
            
            if ($result instanceof PEG_Failure) {
                $context->seek($offset);
                return $ret;
            }
            elseif (!is_null($result)) {
                $ret[] = $result;
            }
        } while (!$context->eos());
        return $ret;
    }
}   
