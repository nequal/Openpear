<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Memoize implements PEG_IParser
{
    protected $parser;
    
    function __construct(PEG_IParser $p)
    {
        $this->parser = $p;
    }
    
    function parse(PEG_IContext $context)
    {
        list($hit, list($end, $val)) = $context->cache($this);
        if ($hit) {
            $context->seek($end);
            return $val;
        }
        $start = $context->tell();
        $val = $this->parser->parse($context);
        $end = $context->tell();
        $context->save($this, $start, $end, $val);
        return $val;
    }
}