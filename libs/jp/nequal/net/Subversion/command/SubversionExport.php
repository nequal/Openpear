<?php
module('command.SubversionCommand');
module('exception.SubversionExportException');

class SubversionExport extends SubversionCommand
{
    protected $_command_ = 'export';
    static protected $_lang_ = 'C';
}
