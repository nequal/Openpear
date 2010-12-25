<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_NoParagraph implements PEG_IParser
{
    protected $parser, $lineParser;

    function __construct(PEG_IParser $element)
    {
        $end = new HatenaSyntax_Regex('#</p><$#');
        $this->lineParser = PEG::many($element);
        $this->parser = PEG::seq(
            PEG::many(
                PEG::subtract(PEG::anything(), $end)
            ),
            $end
        );
    }

    function parse(PEG_IContext $context)
    {
        if ($context->eos()) {
            return PEG::failure();
        }

        $line = $context->readElement();

        if (!preg_match('#^><p( +class="([^"]+)")?>#', $line, $matches)) {
            return PEG::failure();
        }

        $line = substr($line, strlen($matches[0]));
        $attr = isset($matches[2]) && $matches[2] !== '' 
            ? array('class' => $matches[2])
            : array();

        // ><p>~~</p><みたいに一行で終わってるとき
        if (substr($line, -5, 5) === '</p><') {
            $line = substr($line, 0, -5);
            $body = $this->lineParser->parse(PEG::context($line));

            return array('p', $attr, $body);
        }

        $rest = $this->parser->parse($context);

        if ($rest instanceof PEG_Failure) {
            return $rest;
        }

        $line .= join(PHP_EOL, $rest[0]);
        $line .= substr($rest[1], 0, -5);
        
        $body = $this->lineParser->parse(PEG::context($line));

        return array('p', $attr, $body);
    }
}
