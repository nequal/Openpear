<?php
/**
 * @package HatenaSyntax
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class HatenaSyntax_NodeCreater implements PEG_IParser
{
    protected $type, $keys, $parser;
    function __construct($type, PEG_IParser $parser, Array $keys = array())
    {
        $this->type = $type;
        $this->keys = $keys;
        $this->parser = $parser;
    }
    function parse(PEG_IContext $context)
    {
        $offset = $context->tell();
        $result = $this->parser->parse($context);

        if ($result instanceof PEG_Failure) {
            return $result;
        }
        
        $data = array();
        if (count($this->keys) > 0) foreach ($this->keys as $i => $key) {
            $data[$key] = $result[$i];
        }
        else {
            $data = $result;
        }

        return new HatenaSyntax_Node(
            $this->type, $data, $offset, spl_object_hash($context));
    }
}
