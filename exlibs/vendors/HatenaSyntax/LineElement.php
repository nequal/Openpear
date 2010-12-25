<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_LineElement implements PEG_IParser
{
    protected $table;

    function __construct(PEG_IParser $bracket, PEG_IParser $footnote, PEG_IParser $inlinetag)
    {
        $this->table = array(
            '[' => PEG::choice($bracket, PEG::anything()),
            '(' => PEG::choice($footnote, PEG::anything()),
            '<' => PEG::choice($inlinetag, PEG::anything())
        );
    }

    function parse(PEG_IContext $context)
    {
        if ($context->eos()) {
            return PEG::failure();
        }

        $char = $context->readElement();

        if (isset($this->table[$char])) {
            $offset = $context->tell() - 1;
            $context->seek($offset);

            return $this->table[$char]->parse($context);
        }

        return $char;
    }
}
