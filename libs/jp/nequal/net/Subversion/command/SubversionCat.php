<?php
module('command.SubversionCommand');
module('exception.SubversionCatException');

class SubversionCat extends SubversionCommand
{
    protected $_command_ = 'cat';
}
