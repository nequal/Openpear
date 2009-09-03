<?php
import('jp.nequal.net.Subversion');
import('jp.nequal.net.Subversion.exception.SubversionPropgetException');

class SubversionPropget extends Subversion
{
    protected $_command_ = 'propget';
}
