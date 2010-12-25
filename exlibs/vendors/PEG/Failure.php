<?php
/**
 * @package PEG
 * @author anatoo<anatoo@nequal.jp>
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 * @version $Id$
 */

class PEG_Failure
{
    private function __construct(){ } 
    
    static function it()
    {
        static $o = null;
        return $o ? $o : $o = new self;
    }
}