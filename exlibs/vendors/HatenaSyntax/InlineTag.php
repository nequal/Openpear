<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_InlineTag implements PEG_IParser
{
    protected $parser;

    function __construct(PEG_IParser $element)
    {
        $open = PEG::second('<', PEG::choice('del', 'strong', 'inc', 'em'), '>');
        $close = PEG::second('</', PEG::choice('del', 'strong', 'inc', 'em'), '>');
        $this->parser = PEG::seq($open, PEG::many(PEG::subtract($element, $close)), $close);
    }

    function parse(PEG_IContext $context)
    {
        $result = $this->parser->parse($context);

        if ($result instanceof PEG_Failure) {
            return $result;
        }

        list($open, $body, $close) = $result;

        return $open !== $close ? PEG::failure() : array($open, $body);
    }
}
