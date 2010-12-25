<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_SuperPre implements PEG_IParser
{
    protected $parser;

    function __construct()
    {
        $end = new HatenaSyntax_Regex('/\|\|<$/');
        $this->parser = PEG::callbackAction(
            array($this, 'map'), 
            PEG::seq(
                $this->header(),
                PEG::many(PEG::subtract(PEG::anything(), $end)),
                $end
            )
        );
    }

    function parse(PEG_IContext $context)
    {
        return $this->parser->parse($context);
    }

    function map(Array $superpre)
    {
        list($type, $body, $end) = $superpre;

        if ($end !== '||<') {
            $body[] = substr($end, 0, -3);
        }

        return array($type, $body);
    }

    function header()
    {
        return PEG::callbackAction(
            array($this, 'mapHeader'),
            PEG::anything());
    }

    function mapHeader($line)
    {
        if (!preg_match('/^>\|([a-zA-Z0-9]*)\|$/', $line, $matches)) {
            return PEG::failure();
        }

        return $matches[1];
    }
}
