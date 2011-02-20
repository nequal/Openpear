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
class PEAR_PackageProjector_DirectoryEntry_Directory implements PEAR_PackageProjector_DirectoryEntry {
    private $name;
    private $list;
    private $parent;
    
    /**
     *
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->list = array();
    }
    
    /**
     *
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     *
     */
    public function getParent()
    {
        return $this->parent;
    }
    
    /**
     *
     */
    public function setParent(PEAR_PackageProjector_DirectoryEntry $parent)
    {
        $this->parent = $parent;
    }
    
    /**
     *
     */
    public function getAttribute(PEAR_PackageProjector_Project $project)
    {
        return $project->getAttribute($this->getPath());
    }
    
    /**
     *
     */
    public function add(PEAR_PackageProjector_DirectoryEntry $entry)
    {
        $this->list[] = $entry;
        $entry->setParent($this);
        //
        return $entry;
    }

    /**
     *
     */
    public function getPath()
    {
        return (is_null($this->parent))
             ? $this->name.'/'
             : $this->parent->getPath().$this->name.'/';
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
