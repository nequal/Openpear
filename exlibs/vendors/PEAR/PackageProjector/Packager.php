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
class PEAR_PackageProjector_Packager extends PEAR_Packager {
    private $mk_mssghandler;
    
    function setMessageHandler(PEAR_PackageProjector_MessageHandler $handler)
    {
        $this->mk_mssghandler = $handler;
    }
    
    /**
     * Logging method.
     *
     * @param int    $level  log level (0 is quiet, higher is noisier)
     * @param string $msg    message to write to the log
     *
     * @return void
     *
     * @access public
     * @static
     */
    function log($level, $msg, $append_crlf = true)
    {
        $this->mk_mssghandler->buildMessage($level, $msg, $append_crlf);
        /*//
        if ($this->debug >= $level) {
            if (!class_exists('PEAR_Frontend')) {
                require_once 'PEAR/Frontend.php';
            }
            $ui = &PEAR_Frontend::singleton();
            if (is_a($ui, 'PEAR_Frontend')) {
                $ui->log($msg, $append_crlf);
            } else {
                print "$msg\n";
            }
        }
        //*/
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
