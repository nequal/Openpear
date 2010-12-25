<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Delay implements PEG_IParser
{
    protected $callback;

    function __construct($callback)
    {
        $this->callback = $callback;
    }

    function parse(PEG_IContext $context)
    {
        static $parser = null;
        if (!$parser) {
            $parser = call_user_func($this->callback);
            if (!$parser instanceof PEG_IParser) {
                throw new Exception('$parser must be instance of PEG_IParser');
            }
        }

        return $parser->parse($context);
    }
}
