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
require_once 'System.php';
require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR/PackageFileManager/File.php';
require_once 'PEAR/Packager.php';
require_once 'PEAR/Exception.php';
require_once 'PHP/CodeSniffer.php';
require_once 'PEAR/Task/Postinstallscript/rw.php';

//
require_once 'PEAR/PackageProjector/Project.php';
require_once 'PEAR/PackageProjector/Packager.php';
require_once 'PEAR/PackageProjector/Package.php';
require_once 'PEAR/PackageProjector/Visitor.php';
require_once 'PEAR/PackageProjector/Derictory.php';
require_once 'PEAR/PackageProjector/ProjectInfo.php';
require_once 'PEAR/PackageProjector/MessageHandler.php';
require_once 'PEAR/PackageProjector/ConfigureManeger.php';
require_once 'PEAR/PackageProjector/Configure.php';
require_once 'PEAR/PackageProjector/Document.php';

//
require_once 'PEAR/PackageProjector/MessageHandler/Echo.php';
require_once 'PEAR/PackageProjector/MessageHandler/Callback.php';
//
require_once 'PEAR/PackageProjector/DirectoryEntry.php';
require_once 'PEAR/PackageProjector/DirectoryEntry/Root.php';
require_once 'PEAR/PackageProjector/DirectoryEntry/Directory.php';
require_once 'PEAR/PackageProjector/DirectoryEntry/File.php';
//
require_once 'PEAR/PackageProjector/Configure/File.php';
require_once 'PEAR/PackageProjector/Configure/Role.php';
require_once 'PEAR/PackageProjector/Configure/Package.php';
require_once 'PEAR/PackageProjector/Configure/License.php';
require_once 'PEAR/PackageProjector/Configure/Version.php';
require_once 'PEAR/PackageProjector/Configure/Maintainer.php';
require_once 'PEAR/PackageProjector/Configure/Dependency.php';
require_once 'PEAR/PackageProjector/Configure/Project.php';
require_once 'PEAR/PackageProjector/Configure/Installer.php';
require_once 'PEAR/PackageProjector/Configure/Document.php';
//
require_once 'PEAR/PackageProjector/ProjectInfo/License.php';
require_once 'PEAR/PackageProjector/ProjectInfo/ReleaseVersion.php';
require_once 'PEAR/PackageProjector/ProjectInfo/APIVersion.php';
require_once 'PEAR/PackageProjector/ProjectInfo/BaseInstallDir.php';
require_once 'PEAR/PackageProjector/ProjectInfo/PackageName.php';
require_once 'PEAR/PackageProjector/ProjectInfo/PackageType.php';
require_once 'PEAR/PackageProjector/ProjectInfo/PhpDep.php';
require_once 'PEAR/PackageProjector/ProjectInfo/PearinstallerDep.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Channel.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Description.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Summary.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Notes.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Maintainer.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Maintainers.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Role.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Roles.php';
require_once 'PEAR/PackageProjector/ProjectInfo/AttributeManager.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Attribute.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Attribute.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Dependencies.php';
require_once 'PEAR/PackageProjector/ProjectInfo/Dependency.php';
require_once 'PEAR/PackageProjector/ProjectInfo/InstallGroups.php';
require_once 'PEAR/PackageProjector/ProjectInfo/InstallGroup.php';
require_once 'PEAR/PackageProjector/ProjectInfo/InstallParam.php';

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
class PEAR_PackageProjector {
    /**
     *
     */
    static private $singleton;
    private $mkdir_mod;
    private $confgmgr;
    private $mssghandler;
    
    /**
     *
     */
    private function __construct()
    {
        $this->mkdir_mod = 0755;
        //
        $this->setMessageHandler(new PEAR_PackageProjector_MessageHandler_Echo());
        //
        $this->confgmgr = new PEAR_PackageProjector_ConfigureManeger();
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_File());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Dependency());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Package());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_License());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Version());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Maintainer());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Role());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Project());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Installer());
        $this->confgmgr->addConfigure(new PEAR_PackageProjector_Configure_Document());
    }
    
    /**
     *
     */
    static public function singleton()
    {
        if (is_null(self::$singleton)) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }
    
    /**
     *
     */
    public function load($projectpath)
    {
        return new PEAR_PackageProjector_Project($projectpath);
    }
    
    /**
     *
     */
    public function create($projectpath)
    {
        return new PEAR_PackageProjector_Project($projectpath, $this->mkdir_mod);
    }
    
    /**
     *
     */
    public function setMod($mod)
    {
        if (0<$mod) {
            $this->mkdir_mod = $mod;
            return true;
        }
        return false;
    }
    
    /**
     *
     */
    public function configure(PEAR_PackageProjector_ProjectInfo $projinfo, $conf, $basedir)
    {
        $this->confgmgr->setting($projinfo, $conf, $basedir);
    }
    
    /**
     *
     */
    public function getMod($mod)
    {
        if (0<$mod) {
            $this->mkdir_mod = $mod;
            return true;
        }
        return false;
    }
    
    public function getMessageHandler()
    {
        return $this->mssghandler;
    }
    
    public function setMessageHandler(PEAR_PackageProjector_MessageHandler $handler)
    {
        $this->mssghandler = $handler;
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
