<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionCatException');

class SubversionCat extends Subversion
{
    protected $_command_ = 'cat';
}
