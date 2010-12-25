<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Sequence implements PEG_IParser
{
    protected $parsers = array();
    function __construct(Array $parsers = array())
    {
        foreach ($parsers as $p) $this->with($p);
    }
    protected function with(PEG_IParser $p)
    {
        $this->parsers[] = $p;
        return $this;
    }
    function parse(PEG_IContext $context)
    {
        $ret = array();
        foreach ($this->parsers as $parser) {
            $offset = $context->tell();
            $result = $parser->parse($context);
            if ($result instanceof PEG_Failure) return $result;
            elseif ($result !== null) $ret[] = $result;
        }
        return $ret;
    }
}