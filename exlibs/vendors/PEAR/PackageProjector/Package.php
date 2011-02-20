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
class PEAR_PackageProjector_Package {
    const SETUP_SCRIPT = 'Setup';
    private $pkg;
    private $options;
    
    public function __construct()
    {
        $this->pkg     = new PEAR_PackageFileManager2();
        $this->options = array();
        $this->options['filelistgenerator'] = 'file';
        $this->options['packagefile'] = 'package2.xml';
        $this->options['simpleoutput']      = true;
        $this->postInstall = null;
        $this->Installgroups = array();
        //var_dump(get_class_methods($this->pkg));        
    }
    
    public function accept(PEAR_PackageProjector_Visitor $visitor)
    {
        return $visitor->visit($this);
    }
    
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->pkg, $method), $args);
    }
    
    public function setPackageOption($key, $value)
    {
        $this->options[$key] = $value;
    }
    
    public function getPackageDirectory()
    {
        return $this->options['packagedirectory'];
    }
    
    public function getSetupFilepath()
    {
        return strtr($this->getPackage(), array('_'=>'/')).'/'.self::SETUP_SCRIPT.'.php';
    }
    
    public function getPostinstaller()
    {
        if (is_null($this->postInstall)) {
            $this->postInstall = $this->pkg->initPostinstallScript($this->getSetupFilepath());
        }
        return $this->postInstall;
    }
    
    public function addPostInstallGroup($groupname)
    {
        $this->Installgroups[$groupname] = $groupname;
    }
    
    public function addDirectoryRole($path, $role)
    {
        if (isset($this->options['dir_roles'])) {
            $this->options['dir_roles'] = array();
        }
        $this->options['dir_roles'][$path] = $role;
    }
    
    public function addExceptions($path, $role)
    {
        if (isset($this->options['exceptions'])) {
            $this->options['exceptions'] = array();
        }
        $this->options['exceptions'][$path] = $role;
    }
    
    public function addInstallExceptions($path, $dir)
    {
        if (isset($this->options['installexceptions'])) {
            $this->options['installexceptions'] = array();
        }
        $this->options['installexceptions'][$path] = $dir;
    }
    
    public function addPlatformExceptions($path, $platform)
    {
        if (isset($this->options['platformexceptions'])) {
            $this->options['platformexceptions'] = array();
        }
        $this->options['platformexceptions'][$path] = $platform;
    }

    public function ckeckPostInstaller()
    {
        // check installer
        $installer_path = $this->getPackageDirectory().$this->getSetupFilepath();
        $install_name   = $this->getPackage().'_'.self::SETUP_SCRIPT.'_postinstall';
        $text = '';
        //
        if (!file_exists($installer_path)) {
            //create installer
            $text = PEAR_PackageProjector_ConfigureManeger::getPostInstallerText($install_name, $this->Installgroups);
            file_put_contents($installer_path, $text);
            $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
            $handler->buildMessage(5, "", true);
            $handler->buildMessage(5, "*** Create Installer File ***", true);
            $handler->buildMessage(5, "Create File ".$installer_path, true);
        } else {
            $text = file_get_contents($installer_path);
        }
        //
        if (preg_match("/\n *class +".preg_quote($install_name)."[\s\{]/", $text)) {
            return true;
        } else {
            throw new PEAR_Exception("The name of Installer class should be \"{$install_name}\" in {$installer_path}.");
            return false;
        }
    }
    
    public function build()
    {
        //setup options
        $this->pkg->setOptions($this->options);
        //setup installer
        if (!is_null($this->postInstall)) {
            if (false==$this->ckeckPostInstaller()) {
                return false;
            }
            //
            $installer = $this->getSetupFilepath();
            $this->pkg->addPostInstallTask($this->postInstall, $installer);
            $this->pkg->addReplacement($installer, 'pear-config', '@php_bin@', 'php_bin');
            $this->pkg->addReplacement($installer, 'pear-config', '@bin_dir@', 'bin_dir');
            $this->pkg->addReplacement($installer, 'pear-config', '@php_dir@', 'php_dir');
        }
        $this->pkg->addRelease();

        $this->pkg->generateContents();
        // for debug dump:
        //$result = $this->pkg->debugPackageFile();
        $result = $this->pkg->writePackageFile();
        if (PEAR::isError($result)) {
            throw new PEAR_Exception($result->getMessage(), $result->getCode());
        }
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
