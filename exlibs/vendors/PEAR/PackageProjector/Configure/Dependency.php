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
class PEAR_PackageProjector_Configure_Dependency implements PEAR_PackageProjector_Configure {
    /**
     *
     */
    private $name;
    private $type;
    private $channel;
    private $min;
    private $max;
    private $recommended;
    private $exclude;
    private $providesextension;
    private $nodefault;
    
    /**
     *
     */
    public function getName()
    {
        return 'dep://';
    }
    
    /**
     *
     */
    public function start($target, $basedir)
    {
        $this->name = $target;
        // set default
        $this->type              = 'optional';
        $this->channel           = 'pear.php.net';
        $this->min               = false;
        $this->max               = false;
        $this->recommended       = false;
        $this->exclude           = false;
        $this->providesextension = false;
        $this->nodefault         = false;
    }
    
    /**
     *
     */
    public function setting(PEAR_PackageProjector_ProjectInfo $projinfo, $key, $value)
    {
        switch($key) {
        case 'type':
            $this->type = $value;
            return true;
        case 'channel':
            $this->channel = $value;
            return true;
        case 'min':
            $this->min = $value;
            return true;
        case 'max':
            $this->max = $value;
            return true;
        case 'recommended':
            $this->recommended = $value;
            return true;
        case 'exclude':
            $this->exclude = $value;
            return true;
        case 'providesextension':
            $this->providesextension = $value;
            return true;
        case 'nodefault':
            $this->nodefault = $value;
            return true;
        }
        return false;
    }
       
    /**
     *
     */
    public function finish(PEAR_PackageProjector_ProjectInfo $projinfo)
    {
        $dep = new PEAR_PackageProjector_ProjectInfo_Dependency($this->type, $this->name, $this->channel, $this->min, $this->max, $this->recommended, $this->exclude, $this->providesextension, $this->nodefault);
        $projinfo->addPackageDepWithChannel($dep); 
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
