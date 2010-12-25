<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Curry
{
    protected $args, $callback;
    
    protected function __construct($callback, Array $args)
    {
        $this->callback = $callback;
        $this->args = $args;
    }
    function invoke()
    {
        $args = func_get_args();
        return call_user_func_array($this->callback, array_merge($this->args, $args));
    }
    
    static function make($callback)
    {
        $args = func_get_args();
        array_shift($args);
        $curry = new self($callback, $args);
        return array($curry, 'invoke');
    }
}