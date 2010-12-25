<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Not implements PEG_IParser
{
    protected $parser;
    function __construct(PEG_IParser $p)
    {
        $this->parser = $p;
    }
    function parse(PEG_IContext $context)
    {
        if ($context->eos()) return PEG::failure();
        $offset = $context->tell();

        $result = $this->parser->parse($context);
        if ($result instanceof PEG_Failure) {
            $i = $context->tell() - $offset;
            $context->seek($offset);
            $ret =  $context->read($i);
            $context->seek($offset);
            return $ret;
        }
        return PEG::failure();
    }
}