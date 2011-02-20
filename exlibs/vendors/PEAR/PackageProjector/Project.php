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
class PEAR_PackageProjector_Project {
    private $ProjectInfo;
    private $ProjectDirectory;

    /**
     *
     */
    public function __construct($projectfile, $mod=0000)
    {
        if (0<$mod) {
            $this->_createProject($projectfile, $mod);
        } else {
            $this->_loadProject($projectfile);
        }
    }
    
    /**
	 * Serialize PEAR_PackageProjector_ProjectInfo data
     * @return string serialize data
     */
    public function serialize()
    {
        return serialize($this->ProjectInfo);
    }
    
    /**
	 * Unserialize string of PEAR_PackageProjector_ProjectInfo serialize data
     * @return void
     */
    public function unserialize($buff)
    {
        $this->ProjectInfo = unserialize($buff);
    }
    
    /**
     * @return PEAR_PackageProjector_ProjectInfo
     */
    public function info()
    {
        return $this->ProjectInfo;
    }

    /**
     * Build
	 * @return void
     */
    public function make()
    {
        $this->_buildProject();
    }
    
    /**
	 * Load Configure data
     * @param mixed $conf_data configure filepath or array data
	 * @param string $basedir base directory that load files(desc.txt,notes.txt,etc..).
	 * @return boolean
     */
    public function configure($conf_data, $basedir=null)
    {
		$handler = PEAR_PackageProjector::singleton()->getMessageHandler();
		$handler->buildMessage(5, "*** Configuring package. ***", true);
		
        $this->ProjectInfo = new PEAR_PackageProjector_ProjectInfo();

		if (is_null($basedir)) {
			$basedir = $this->ProjectDirectory->getBaseDir();
		}
		if (is_array($conf_data)) {
			PEAR_PackageProjector::singleton()->configure($this->ProjectInfo, $conf_data, $basedir);
		} else {
			$confpath = PEAR_PackageProjector_Derictory::getRealpath($conf_data, $basedir);
			if (!file_exists($confpath)) {
				throw new PEAR_Exception("Not Found build configure file( ".$conf_data." )", PEAR_ERROR_EXCEPTION);
				return false;
			}
			$conf = parse_ini_file($confpath, true);
			PEAR_PackageProjector::singleton()->configure($this->ProjectInfo, $conf, $basedir);
		}
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, 0)) {
            return false;
        }
        $handler->buildMessage(5, "", true);
		return true;
    }
    
    /**
     * Check code by CodeSniffer.
	 * @return boolean
     */
    public function checkcode()
    {
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, 0)) {
            return ;
        }
        $oldcwd = getcwd();
        $verbose = false;
        $files = $this->ProjectDirectory->getSrcPath();
        $standard = 'PEAR';
        $report = '';
        $showWarnings = false;

        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        /*
         * execute code sniffer
         */
        $handler->buildMessage(5, "*** Checks source in CodeSniffer. ***", true);
        //
        ob_start();
        $phpcs = new PHP_CodeSniffer($verbose);
        $phpcs->process($files, $standard);
        if ($report === 'summary') {
            $phpcs->printErrorReportSummary($showWarnings);
        } else {
            $phpcs->printErrorReport($showWarnings);
        }
        $buff = ob_get_contents();
        ob_end_clean();
        $handler->buildMessage(5, $buff, true);
        
        chdir($oldcwd);
        $handler->buildMessage(5, "*** Finished checked source in CodeSniffer. ***", true);
        return (0==strlen($buff));
    }
    
    /**
     * Update Document
	 * @return boolean
     */
    public function updatedoc()
    {
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, 0)) {
            return false;
        }
        $srcpath = $this->ProjectDirectory->getSrcPath();
        $docpath = $this->ProjectDirectory->getDocumentPath();
        $doc = new PEAR_PackageProjector_Document($docpath);
        
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
       
        /*
         * Create document files
         */
        $handler->buildMessage(5, "*** Create document files ***", true);
        //
        $doc->accept($this->ProjectDirectory->getPackageDirectory());
        $doc->accept($this->ProjectInfo);
        $doc->build();
        $handler->buildMessage(5, "*** Finished created document files ***", true);
        return true;
    }
    
    /**
     * Execute PEAR Install
	 * @param string $version Install Version
	 * @return boolean
     */
    public function pearinstall($version=null)
    {
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, 0)) {
            return false;
        }
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        $pkgfile = $this->ProjectInfo->getPackageFileName($version);

        $handler->buildMessage(5, "*** Install ".$pkgfile." ***", true);
        $filepath = $this->ProjectDirectory->getRelasePath().$pkgfile;
        $cmd = 'pear install -a -f "'.$filepath.'"';
        //$handler->buildMessage(5, $cmd, true);
        ob_start();
        system($cmd);
        $buff = ob_get_contents();
        ob_end_clean();
        $handler->buildMessage(5, $buff, true);
        
        return true;
    }

    /**
     *
     */
    private function _createProject($projectpath, $mod)
    {
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        //
        $this->ProjectInfo = new PEAR_PackageProjector_ProjectInfo();
        
        //
        $this->ProjectDirectory = new PEAR_PackageProjector_Derictory($projectpath);
        if (false === $this->ProjectDirectory->checkCreateProject()) {
            return;
        }
        
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, $mod)) {
            return ;
        }
        //
        $pathinfo = pathinfo($projectpath);
        //
        $this->ProjectDirectory->createBuildConf($pathinfo['basename']);
        $this->ProjectDirectory->createBuildScript();
        $this->ProjectDirectory->createDocScript();
        //$this->ProjectDirectory->createReadme();
        $this->ProjectDirectory->createSrcDir($pathinfo['basename']);
        $this->ProjectDirectory->createBaseSrc($pathinfo['basename']);
        $this->ProjectDirectory->createSampleSrc($pathinfo['basename']);
        $this->ProjectDirectory->createNotesText();
        $this->ProjectDirectory->createDescText();
        $this->ProjectDirectory->createTutorialText();
        
        $handler->buildMessage(5, "create project directory ".$this->ProjectDirectory->getBaseDir().".", true);
    }
        
    /**
     *
     */
    private function _loadProject($projectpath)
    {
        //
        $this->ProjectDirectory = new PEAR_PackageProjector_Derictory($projectpath);
        if (false === $this->ProjectDirectory->checkLoadProject()) {
            return;
        }
		$this->ProjectInfo = new PEAR_PackageProjector_ProjectInfo();
                
        return true;
    }
    
    /**
     *
     */
    private function _buildProject()
    {
        //
        if (!$this->ProjectDirectory->loadSetting($this->ProjectInfo, 0)) {
            return ;
        }
        $package = new PEAR_PackageProjector_Package();
        $oldcwd  = getcwd();
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        //
        try {
            chdir($this->ProjectDirectory->getSrcPath());
            /*
             * Create package2.xml
             */
            $handler->buildMessage(5, "*** Create package2.xml ***", true);
            //
            $package->accept($this->ProjectDirectory->getPackageDirectory());
            $package->accept($this->ProjectInfo);
            $package->build();
            $handler->buildMessage(5, "", true);

            /*
             * Create Tgz
             */
            $handler->buildMessage(5, "*** Create Tgz File ***", true);
            //
            chdir($this->ProjectDirectory->getRelasePath());
            $pkg = new PEAR_PackageProjector_Packager();
            $pkg->setMessageHandler($handler);
            $pkg->package($this->ProjectDirectory->getPackageFile());
            $handler->buildMessage(5, "", true);
            
        } catch(Exception $e) {
            chdir($oldcwd);
            $handler->buildMessage(5, "\n*** Stop for the exception was generated.  ***", true);
            throw $e;
        }
        
        $handler->buildMessage(5, "*** Finish ***", true);

        //
        chdir($oldcwd);
        
        //
        return true;
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
