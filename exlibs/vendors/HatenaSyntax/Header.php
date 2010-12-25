<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Header implements PEG_IParser
{
    protected $child, $parser;

    function __construct(PEG_IParser $elt)
    {
        $this->child = PEG::many($elt);
        $this->parser = PEG::callbackAction(array($this, 'map'), PEG::anything());
    }

    function parse(PEG_IContext $context)
    {
        return $this->parser->parse($context);
    }

    function map($line)
    {
        if (strpos($line, '*') === 0) {
            list($level, $rest) = $this->toLevelAndRest((string)substr($line, 1));

            list($name, $rest) = $level === 0 
                ? $this->toNameAndRest($rest) 
                : array(false, $rest);

            $body = $this->child->parse(PEG::context($rest));

            return array($level, $name, $body);
        }

        return PEG::failure();
    }

    protected function toLevelAndRest($line)
    {
        $level = 0;

        for ($i = 0, $len = strlen($line); $i < $len; $i++) {
            if ($line[$i] === '*') {
                $level++;
            } else {
                break;
            }
        }

        return array($level, (string)substr($line, $level));
    }

    protected function toNameAndRest($rest)
    {
        if (preg_match('/^([-[:alnum:]_]*)\*/', $rest, $matches)) {
            return array($matches[1], (string)substr($rest, strlen($matches[0])));
        }

        return array(false, $rest);
    }
}
