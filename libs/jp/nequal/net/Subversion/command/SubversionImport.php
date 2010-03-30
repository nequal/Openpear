<?php
module('command.SubversionCommand');
module('exception.SubversionImportException');

class SubversionImport extends SubversionCommand
{
    protected $_command_ = 'import';
    static protected $_lang_ = 'C';
}
