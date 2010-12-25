<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Paragraph implements PEG_IParser
{
    protected $parser, $line;

    function __construct(PEG_IParser $lineelt)
    {
        $this->parser = PEG::callbackAction(
            array($this, 'map'),
            PEG::anything()
        );
        $this->line = PEG::many($lineelt);
    }

    function parse(PEG_IContext $context)
    {
        return $this->parser->parse($context);
    }

    function map($line)
    {
        return $this->line->parse(PEG::context($line));
    }

}
