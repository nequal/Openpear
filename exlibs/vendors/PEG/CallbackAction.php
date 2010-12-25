<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_CallbackAction implements PEG_IParser 
{
    protected $callback, $parser;

    function __construct($callback, PEG_IParser $parser)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('first argument must be callable');
        }

        list($this->callback, $this->parser) = func_get_args();
    }

    function parse(PEG_IContext $context)
    {
        $buf = $this->parser->parse($context);

        if ($buf instanceof PEG_Failure) {
            return $buf;
        }

        return call_user_func($this->callback, $buf);
    }
}
