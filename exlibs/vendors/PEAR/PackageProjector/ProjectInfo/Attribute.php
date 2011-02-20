<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Creates PEAR Package in the way like the Command "make".
 * 
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * 
 * @category   pear
 * @package    PEAR_PackageProjector
 * @author     Kouichi Sakamoto <sakamoto@servlet.sakura.ne.jp> 
 * @copyright  2007 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/PEAR_PackageProjector
 * @since      File available since Release 0.1.0
 */

/**
 * 
 *
 * @category   pear
 * @package    PEAR_PackageProjector
 * @author     Kouichi Sakamoto <sakamoto@servlet.sakura.ne.jp> 
 * @copyright  2007 The PHP Group
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    Release: 0.1.0
 * @link       http://pear.php.net/package/PEAR_PackageProjector
 * @since      Class available since Release 0.1.0
 */
class PEAR_PackageProjector_ProjectInfo_Attribute implements PEAR_PackageProjector_Visitor {
    private $path;
    private $attrs;

    /**
     *
     */
    public function __construct($path)
    {
        $this->path  = $path;
        $this->attrs = array();
    }

    /**
     *
     */
    private function _getAttr($key)
    {
        return (isset($this->attrs[$key])) ? $this->attrs[$key] : null;
    }
    
    /**
     *
     */
    private function _setAttr($key, $value)
    {
        if (is_null($value)) {
            unset($this->attrs[$key]);
        } else {
            $this->attrs[$key] = $value;
        }
    }

    /**
     *
     */
    public function setIgnore($bool)
    {
        $this->_setAttr('ignore', ((true==$bool)?true:null));
    }

    /**
     *
     */
    public function setPlatform($platform=null)
    {
        $this->_setAttr('platform', $platform);
    }

    /**
     *
     */
    public function setInstall($dir=null)
    {
        $this->_setAttr('install', $dir);
    }

    /**
     * This file change Command Script File
     */
    public function setCommandScript($rename=null)
    {
        if (is_null($rename)) {
            $this->setRole(null);
            $this->setInstall(null);
            $this->clearReplace();
        } else {
            $this->setRole('script');
            $this->setInstall($rename);
            $this->addReplace('pear-config', '@php_bin@', 'php_bin');
            $this->addReplace('pear-config', '@bin_dir@', 'bin_dir');
            $this->addReplace('pear-config', '@php_dir@', 'php_dir');
        }
    }

    /**
     *
     * php (most common)
     * data
     * doc
     * test
     * script (gives the file an executable attribute)
     * src
     */
    public function setRole($role)
    {
        switch($role) {
        case 'php':
        case 'data':
        case 'doc':
        case 'test':
        case 'script':
        case 'src':
            $this->_setAttr('role', $role);
            return ;
        }
        $this->_setAttr('role', null);
    }

    /**
     * Add a replacement option for a file
     *
     * This sets an install-time complex search-and-replace function
     * allowing the setting of platform-specific variables in an
     * installed file.
     *
     * if $type is php-const, then $to must be the name of a PHP Constant.
     * If $type is pear-config, then $to must be the name of a PEAR config
     * variable accessible through a {@link PEAR_Config::get()} method.  If
     * type is package-info, then $to must be the name of a section from
     * the package.xml file used to install this file.
     *
     * @parem string   $type  variable type, either php-const, pear-config or package-info 
     * @parem string   $from  text to replace in the source file 
     * @parem string   $to    variable name to use for replacement 
     */
    public function addReplace($type, $from, $to)
    {
        $list = $this->_getAttr('replace');
        if (!is_array($list)) {
            $list = array();
        }
        
        $list[$type][$from] = $to;
        
        $this->_setAttr('replace', $list);
    }

    /**
     *
     */
    public function clearReplace()
    {
        $this->_setAttr('replace', null);
    }
    
    /**
     *
     */
    public function visit(PEAR_PackageProjector_Package $package)
    {
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        //
        $dir = $package->getPackageDirectory();
        $fullpath = realpath($dir.'/'.$this->path);
        //
        if (true===$this->_getAttr('ignore')) {
            $handler->buildMessage(5, "Add attribute 'ignore' of [{$this->path}]... yes", true);
            if (is_dir($fullpath)) {
                $package->addIgnore($fullpath.DIRECTORY_SEPARATOR.'*');
            } else {
                $package->addIgnore($fullpath);
            }
        }
        //
        $role = $this->_getAttr('role');
        if (!is_null($role)) {
            $handler->buildMessage(5, "Add attribute 'role' of [{$this->path}]... {$role}", true);
            if (is_dir($fullpath)) {
                $package->addDirectoryRole($this->path, $role);
            } else {
                $package->addExceptions($this->path, $role);
            }
        }
        //
        $platform = $this->_getAttr('platform');
        if (!is_null($platform)) {
            $handler->buildMessage(5, "Add attribute 'platform' of [{$this->path}]... {$platform}", true);
            $package->addPlatformExceptions($this->path, $platform);
        }
        //
        $install = $this->_getAttr('install');
        if (!is_null($install)) {
            $handler->buildMessage(5, "Add attribute 'install' of [{$this->path}]... {$install}", true);
            $package->addInstallAs($this->path, $install);
            $package->addInstallExceptions($this->path, '/');
        }
        //
        $replace = $this->_getAttr('replace');
        if (is_array($replace)) {
            foreach ($replace as $type=>$value) {
                foreach ($value as $from=>$to) {
                    $handler->buildMessage(5, "Add attribute 'replace' of [{$this->path}]... {$from} => {$type}::{$to}", true);
                    $package->addReplacement($this->path, $type, $from, $to);
                }
            }
        }
    }
       
    /**
     *
     */
    public function visitDocument(PEAR_PackageProjector_Document $doc)
    {
        ;
    }

}

/*
 * Local Variables:
 * mode: php
 * tab-width: 4
 * c-basic-offset: 4
 * End:
 */
?>
