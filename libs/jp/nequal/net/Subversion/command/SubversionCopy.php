<?php
module('command.SubversionCommand');
module('exception.SubversionCopyException');

class SubversionCopy extends SubversionCommand
{
    protected $_command_ = 'copy';
    static protected $_lang_ = 'C';
}
