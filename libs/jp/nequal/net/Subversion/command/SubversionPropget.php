<?php
module('command.SubversionCommand');
module('exception.SubversionPropgetException');

class SubversionPropget extends SubversionCommand
{
    protected $_command_ = 'propget';
}
