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
class PEAR_PackageProjector_Derictory {
    /**
     *
     */
    private $Basedir;
    /**
     *
     */
    private $Srcdir;
    /**
     *
     */
    private $Releasedir;
    /**
     *
     */
    private $Docdir;
    /**
     *
     */
    private $PackageDirectory;
    /**
     *
     */
    private $ProjectFilePath;
    
    /**
     *
     */
    public function __construct($projectpath)
    {
        $path = self::getRealpath($projectpath);
        if (is_file($path)) {
            $path = dirname($path);
        }
        $this->Basedir = $path.DIRECTORY_SEPARATOR;
    }

    /**
     *
     */
    public function loadSetting(PEAR_PackageProjector_ProjectInfo $projinfo, $mod=0755)
    {
        $src = $projinfo->getProjectSrcDir().'/';
        $res = $projinfo->getProjectReleaseDir().'/';
        $doc = $projinfo->getDocumentDir().'/';

        $this->Srcdir     = (self::isAbsolutePath($src)) ? $src : $this->Basedir.$src;
        $this->Releasedir = (self::isAbsolutePath($res)) ? $res : $this->Basedir.$res;
        $this->Docdir     = (self::isAbsolutePath($res)) ? $res : $this->Basedir.$doc;
        
        //
        return $this->_check($mod);
    }
       
    /**
     *
     */
    public function checkCreateProject()
    {
        if (is_dir($this->Basedir)) {
            throw new PEAR_Exception($this->Basedir." exists already.", PEAR_ERROR_EXCEPTION);
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function checkLoadProject()
    {
        if (!is_dir($this->Basedir)) {
            throw new PEAR_Exception($this->Basedir." doesn't exist.", PEAR_ERROR_EXCEPTION);
            return false;
        }
        return true;
    }

    /**
     *
     */
    public function getPackageFile()
    {
        $dir = $this->PackageDirectory->getName();
        return $dir.'/package2.xml';
    }
    
    /**
     *
     */
    public function getPackageDirectory()
    {
        if (is_null($this->PackageDirectory)) {
            $this->PackageDirectory = new PEAR_PackageProjector_DirectoryEntry_Root($this->getSrcPath());
        }
        return $this->PackageDirectory;
    }
    
    /**
     *
     */
    public function getBaseDir()
    {
        return $this->Basedir;
    }

    /**
     *
     */
    public function getSrcPath()
    {
        return $this->Srcdir;
    }

    /**
     *
     */
    public function getRelasePath()
    {
        return $this->Releasedir;
    }

    /**
     *
     */
    public function getDocumentPath()
    {
        return $this->Docdir;
    }

    /**
     *
     */
    public function createBuildConf($package_name)
    {
        $text = PEAR_PackageProjector_ConfigureManeger::getBuildConfigureText($package_name);
        file_put_contents($this->Basedir.'build.conf', $text);
    }
    
    /**
     *
     */
    public function createSrcDir($package_name)
    {
        $path = $this->Srcdir.strtr($package_name, array('_'=>'/'));
        return System::mkdir(array('-p', $path));
    }
    
    /**
     *
     */
    public function createBaseSrc($package_name)
    {
        $path = $this->Srcdir.strtr($package_name, array('_'=>'/')).'.php';
        if (is_file($path)) {
            return true;
        }
        $text  = '<?php'."\n";
        $text .= 'class '.$package_name.' {'."\n";
        $text .= '    function __construct()'."\n";
        $text .= '    {'."\n";
        $text .= '        ;'."\n";
        $text .= '    }'."\n";
        $text .= '}'."\n";
        $text .= '?>'."\n";
        return file_put_contents($path, $text);
    }
    
    /**
     *
     */
    public function createSampleSrc($package_name)
    {
        $inc_path = strtr($package_name, array('_'=>'/')).'.php';
        $path = $this->Basedir.'sample.php';
        if (is_file($path)) {
            return true;
        }
        $text  = '<?php'."\n";
        $text .= 'ini_set("include_path", dirname(__FILE__)."/src/" . PATH_SEPARATOR . ini_get("include_path"));'."\n";
        $text .= 'require_once "'.$inc_path.'";'."\n";
        $text .= "\n";
        $text .= '// Test'."\n";
        $text .= '$obj = new '.$package_name.'();'."\n";
        $text .= "\n";
        $text .= "\n";
        $text .= "\n";
        $text .= '?>'."\n";
        return file_put_contents($path, $text);
    }
    
    /**
     *
     */
    public function createNotesText()
    {
        $text  = '[0.1.0]'."\n";
        $text .= "- First release\n";
        return file_put_contents($this->Basedir.'notes.txt', $text);
    }
    
    /**
     *
     */
    public function createDescText()
    {
        return file_put_contents($this->Basedir.'desc.txt', '#');
    }
    
    /**
     *
     */
    public function createTutorialText()
    {
        return file_put_contents($this->Basedir.'tutorial.txt', '**info');
    }
    
    /**
     *
     */
    public function createReadme()
    {
        file_put_contents($this->getSrcPath().'README.TXT', '');
    }
        
    /**
     *
     */
    public function createBuildScript()
    {
        //
        $text1 = PEAR_PackageProjector_ConfigureManeger::getBuildScriptTextWindows();
        file_put_contents($this->Basedir.'build.bat', $text1);
        //
        $text2 = PEAR_PackageProjector_ConfigureManeger::getBuildScriptTextUnix();
        file_put_contents($this->Basedir.'build', $text2);
        chmod($this->Basedir.'build', 0744);
    }
        
    /**
     *
     */
    public function createDocScript()
    {
        //
        $text1 = PEAR_PackageProjector_ConfigureManeger::getDocScriptTextWindows();
        file_put_contents($this->Basedir.'updatedoc.bat', $text1);
        //
        $text2 = PEAR_PackageProjector_ConfigureManeger::getDocScriptTextUnix();
        file_put_contents($this->Basedir.'updatedoc', $text2);
        chmod($this->Basedir.'updatedoc', 0744);
    }

    /**
     *
     */
    private function _check($mod)
    {
        if (false == $this->_mkdir($this->Basedir, $mod)) {
            throw new PEAR_Exception("Not Found Project Direcotry( ".$this->Basedir." )", PEAR_ERROR_EXCEPTION);
            return false;
        }
        if (false == $this->_mkdir($this->Srcdir, $mod)) {
            throw new PEAR_Exception("Not Found Project Source Direcotry( ".$this->Srcdir." )", PEAR_ERROR_EXCEPTION);
            return false;
        }
        if (false == $this->_mkdir($this->Releasedir, $mod)) {
            throw new PEAR_Exception("Not Found Project Release Direcotry( ".$this->Releasedir." )", PEAR_ERROR_EXCEPTION);
            return false;
        }
        //if (false == $this->_mkdir($this->Docdir, $mod)) {
        //    throw new PEAR_Exception("Not Found Project Document Direcotry( ".$this->Docdir." )", PEAR_ERROR_EXCEPTION);
        //    return false;
        //}
        return true;
    }

    /**
     *
     */
    private function _mkdir($dir, $mod)
    {
        if (is_dir($dir)) {
            return true;
        }
        return (0<$mod)?mkdir($dir, $mod):false;
    }

    /**
     *
     */
    static public function getRealpath($path, $pwd=null)
    {
        if (self::isAbsolutePath($path)) {
            return $path;
        }
		if (is_null($pwd)) {
			$pwd = (OS_WINDOWS) ? getcwd() : getenv('PWD');
		}
		if (DIRECTORY_SEPARATOR === substr($pwd, -1,1) || '/' === substr(-1,1)) {
			return $pwd.$path;
		}
        return $pwd.DIRECTORY_SEPARATOR.$path;
    }

    /**
     *
     */
    static public function isAbsolutePath($path)
    {
        if (OS_WINDOWS) {
            if (preg_match('/^[a-z]:/i', $path) && $path{2} == DIRECTORY_SEPARATOR) {
                return true;
            }
        } else {
            if ($path{0} == DIRECTORY_SEPARATOR) {
                return true;
            }
        }
        return false;
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
