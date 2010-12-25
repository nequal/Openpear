<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_Quote implements PEG_IParser
{
    protected $child, $parser;

    function __construct(PEG_IParser $child)
    {
        $this->child = $child;

        $this->parser = PEG::seq(
            PEG::callbackAction(array($this, 'mapHeader'), PEG::anything()),
            PEG::many(PEG::subtract($this->child, '<<')),
            PEG::drop('<<')
        );
    }

    function parse(PEG_IContext $context)
    {
        return $this->parser->parse($context);
    }

    function mapHeader($line)
    {
        if (substr($line, 0, 1) !== '>' || substr($line, -1, 1) !== '>') {
            return PEG::failure();
        }

        if ($line === '>>') {
            return false;
        }

        $link_exp = substr($line, 1, strlen($line) - 2);

        if (!preg_match('#^(https?://[^>:]+)(:title(=(.+))?)?$#', $link_exp, $matches)) {
            return PEG::failure();
        }

        $title = !isset($matches[2]) 
            ? false
            : (isset($matches[4]) ? $matches[4] : '');

        return new HatenaSyntax_Node('httplink', array(
            'href'   => $matches[1], 
            'title' => isset($matches[4]) ? $matches[4] : false)
        );
    }
}
