<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_List implements PEG_IParser
{
    protected $parser, $li;

    function __construct(PEG_IParser $lineelt)
    {
        $item = PEG::callbackAction(
            array($this, 'mapLine'),
            PEG::anything());

        $this->parser = PEG::callbackAction(
            array('HatenaSyntax_Tree', 'make'),
            PEG::many1($item)
        );

        $this->li = PEG::callbackAction(
            array('HatenaSyntax_Util', 'processListItem'),
            PEG::seq(
                PEG::many(PEG::char('+-')),
                PEG::many($lineelt)
            )
        );
    }

    function parse(PEG_IContext $context)
    {
        return $this->parser->parse($context);
    }

    function mapLine($line)
    {
        if (in_array(substr($line, 0, 1), array('+', '-'), true)) {
            return $this->li->parse(PEG::context($line));
        }

        return PEG::failure();
    }
}
