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
class PEAR_PackageProjector_ConfigureManeger {
    private $groups;
    
    /**
     *
     */
    public function __construct()
    {
        $this->groups = array();
    }
    
    /**
     *
	 * @param PEAR_PackageProjector_ProjectInfo $projinfo 
	 * @param array $conf
	 * @param string $basedir
     */
    public function setting(PEAR_PackageProjector_ProjectInfo $projinfo, $conf, $basedir)
    {
        foreach ($conf as $groupname=>$items) {
            $matches = array();
            $type   = $groupname;
            $target = null;
            if (preg_match("/^([a-z0-9\_\-]+:\/\/)(.+)/i", $groupname, $matches)) {
                $type   = $matches[1];
                $target = $matches[2];
            }
            if (isset($this->groups[$type])) {
                //
                $group = $this->groups[$type];
                $group->start($target, $basedir);
                foreach ($items as $key=>$value) {
                    $group->setting($projinfo, $key, $value);
                }
                $group->finish($projinfo);
            }
        }
    }

    /**
     *
     */
    public function addConfigure(PEAR_PackageProjector_Configure $confg )
    {
        $this->groups[$confg->getName()] = $confg;
    }

    /**
     *
     */
    static public function getBuildScriptTextWindows()
    {
        $text = "pearproj -i --configure ./build.conf --make -p ./";
        return $text;
    }

    /**
     *
     */
    static public function getBuildScriptTextUnix()
    {
        $text = "#!/bin/sh\n";
        $text .= "pearproj -i --configure ./build.conf --make -p ./";
        return $text;
    }

    /**
     *
     */
    static public function getDocScriptTextWindows()
    {
        $text = "pearproj -doc --configure ./build.conf -p ./";
        return $text;
    }

    /**
     *
     */
    static public function getDocScriptTextUnix()
    {
        $text = "#!/bin/sh\n";
        $text .= "pearproj -doc --configure ./build.conf -p ./";
        return $text;
    }

    /**
     *
     */
    static public function getBuildConfigureText($package_name)
    {
        $text = '[project]'.PHP_EOL
              . 'src_dir = src'.PHP_EOL
              . 'release_dir = release'.PHP_EOL
              . ''.PHP_EOL
              . '[document]'.PHP_EOL
              . 'doc_dir = doc'.PHP_EOL
              . 'tutorial_file = tutorial.txt'.PHP_EOL
              . ';; if stylesheet_file is "@http://...". it download file.'.PHP_EOL
              . ';stylesheet_file = @http://d.hatena.ne.jp/theme/hatena/hatena.css'.PHP_EOL
              . 'stylesheet_file = '.PHP_EOL
              . ''.PHP_EOL
              . '[package]'.PHP_EOL
              . 'package_name = '.$package_name.PHP_EOL
              . 'package_type = php'.PHP_EOL
              . 'baseinstalldir = /'.PHP_EOL
              . 'channel = __uri'.PHP_EOL
              . 'summary = #'.PHP_EOL
              . ';description = #'.PHP_EOL
              . ';notes = #'.PHP_EOL
              . ';summary_file = <filepath>'.PHP_EOL
              . 'description_file = desc.txt'.PHP_EOL
              . 'notes_file = notes.txt'.PHP_EOL
              . ''.PHP_EOL
              . '[role]'.PHP_EOL
              . ';; role value is <php|data|doc|test|script|src>'.PHP_EOL
              . ';sh = script'.PHP_EOL
              . ''.PHP_EOL
              . '[version]'.PHP_EOL
              . 'release_ver = 0.1.0'.PHP_EOL
              . 'release_stab = alpha'.PHP_EOL
              . 'api_ver = 0.1.0'.PHP_EOL
              . 'api_stab = alpha'.PHP_EOL
              . 'php_min = 5.1.0'.PHP_EOL
              . 'pear_min = 1.4.11'.PHP_EOL
              . ''.PHP_EOL
              . '[license]'.PHP_EOL
              . 'name =PHP License 3.01'.PHP_EOL
              . 'uri = http://www.php.net/license/3_01.txt'.PHP_EOL
              . ''.PHP_EOL
              . '[maintainer://handlename]'.PHP_EOL
              . 'name = fullname'.PHP_EOL
              . 'email = email@local.local'.PHP_EOL
              . 'role = lead'.PHP_EOL
              . ''.PHP_EOL
              . ';[file://<filepath>]'.PHP_EOL
              . ';commandscript = command'.PHP_EOL
              . ';ignore = 1'.PHP_EOL
              . ';platform = windows'.PHP_EOL
              . ';install = renamefile'.PHP_EOL
              . ';; role value is <php|data|doc|test|script|src>'.PHP_EOL
              . ';role = script'.PHP_EOL
              . ''.PHP_EOL
              . ';[dep://<packagename>]'.PHP_EOL
              . ';; type: <required|optional>'.PHP_EOL
              . ';type = optional'.PHP_EOL
              . ';; channel: pear.php.net or __uri or etc...'.PHP_EOL
              . ';channel = pear.php.net'.PHP_EOL
              . ';min = 0'.PHP_EOL
              . ';max = 0'.PHP_EOL
              . ';recommended = 0'.PHP_EOL
              . ';exclude = 0'.PHP_EOL
              . ';providesextension = 0'.PHP_EOL
              . ';nodefault = 0'.PHP_EOL
              . ''.PHP_EOL
              . ';[installer://<groupname>]'.PHP_EOL
              . ';instructions = <group info message>'.PHP_EOL
              . ';<param_name>.prompt = <param info message>'.PHP_EOL
              . ';<param_name>.type = string'.PHP_EOL
              . ';<param_name>.default = <default value>'.PHP_EOL
              . ''.PHP_EOL
              ;
        return $text;
    }

    /**
     *
     */
    static public function getPostInstallerText($name, $groups)
    {
        $text = '<?php'.PHP_EOL
              . 'class '.$name.PHP_EOL
              . '{'.PHP_EOL
              . ''.PHP_EOL
              . '    // {{{ $_config'.PHP_EOL
              . ''.PHP_EOL
              . '    /**'.PHP_EOL
              . '     * PEAR_Config object '.PHP_EOL
              . '     * '.PHP_EOL
              . '     * @var object(PEAR_Config)'.PHP_EOL
              . '     * @access protected'.PHP_EOL
              . '     */'.PHP_EOL
              . '    private $_config;'.PHP_EOL
              . ''.PHP_EOL
              . '    // }}}'.PHP_EOL
              . '    // {{{ $_ui'.PHP_EOL
              . ''.PHP_EOL
              . '    /**'.PHP_EOL
              . '     * PEAR_Installer_Ui '.PHP_EOL
              . '     * '.PHP_EOL
              . '     * @var object(PEAR_Installer_Ui)'.PHP_EOL
              . '     * @access protected'.PHP_EOL
              . '     */'.PHP_EOL
              . '    private $_ui;'.PHP_EOL
              . ''.PHP_EOL
              . '    // }}}'.PHP_EOL
              . ''.PHP_EOL
              . '    // {{{ init()'.PHP_EOL
              . ''.PHP_EOL
              . '    /**'.PHP_EOL
              . '     * init install.'.PHP_EOL
              . '     *'.PHP_EOL
              . '     * @link http://pear.php.net/package/PEAR/docs/1.4.4/PEAR/PEAR_Config.html'.PHP_EOL
              . '     * @link http://pear.php.net/package/PEAR/docs/1.4.4/PEAR/PEAR_PackageFile_v2.html'.PHP_EOL
              . '     * '.PHP_EOL
              . '     * @param object(PEAR_Config) $config'.PHP_EOL
              . '     * @param object(PEAR_PackageFile_v2) $self'.PHP_EOL
              . '     * @param string $lastInstalledVersion'.PHP_EOL
              . '     * @access public'.PHP_EOL
              . '     * @return bool True if initialized successfully, otherwise false.'.PHP_EOL
              . '     */'.PHP_EOL
              . '    function init(&$config, $self, $lastInstalledVersion = null)'.PHP_EOL
              . '    {'.PHP_EOL
              . '       $this->_config = &$config;'.PHP_EOL
              . '       $this->_ui = &PEAR_Frontend::singleton();'.PHP_EOL
              . '       '.PHP_EOL
              . '       return true;'.PHP_EOL
              . '    }'.PHP_EOL
              . ''.PHP_EOL
              . '    // }}}'.PHP_EOL
              . '    // {{{ run()'.PHP_EOL
              . ''.PHP_EOL
              . '    /**'.PHP_EOL
              . '     * Run install.'.PHP_EOL
              . '     * '.PHP_EOL
              . '     * @param array $infoArray'.PHP_EOL
              . '     * @param string $paramGroup'.PHP_EOL
              . '     * @access public'.PHP_EOL
              . '     * @return bool'.PHP_EOL
              . '     */'.PHP_EOL
              . '    function run($infoArray, $paramGroup)'.PHP_EOL
              . '    {'.PHP_EOL
              . '        if (\'_undoOnError\' == $paramGroup) {'.PHP_EOL
              . '            $this->_ui->outputData(\'An error occured during installation.\');'.PHP_EOL
              . '            return false;'.PHP_EOL
              . '        }'.PHP_EOL
              . '        '.PHP_EOL
              . '        $method = \'run_\'.$paramGroup;'.PHP_EOL
              . '        if (method_exists($this, $method)) {'.PHP_EOL
              . '            return $this->$method($infoArray);'.PHP_EOL
              . '        }'.PHP_EOL
              . '        '.PHP_EOL
              . '        $this->_ui->outputData(\'ERROR: Unknown parameter group <\'.$paramGroup.\'>.\');'.PHP_EOL
              . '        return false;'.PHP_EOL
              . '    }'.PHP_EOL
              . ''.PHP_EOL
              . '    // }}}'.PHP_EOL;
              //
              foreach ($groups as $num=>$groupname) {
                  $text .= self::getPostInstallerMethodText($groupname);
              }

        return $text. '}'.PHP_EOL. '?>'.PHP_EOL;
    }

    /**
     *
     */
    static public function getPostInstallerMethodText($groupname)
    {
        $text = '    // {{{ run_'.$groupname.'()'.PHP_EOL
              . ''.PHP_EOL
              . '    /**'.PHP_EOL
              . '     * Run '.$groupname.' install.'.PHP_EOL
              . '     * '.PHP_EOL
              . '     * @param array $infoArray'.PHP_EOL
              . '     * @access public'.PHP_EOL
              . '     * @return bool'.PHP_EOL
              . '     */'.PHP_EOL
              . '    function run_'.$groupname.'($infoArray)'.PHP_EOL
              . '    {'.PHP_EOL
              . '        return true;'.PHP_EOL
              . '    }'.PHP_EOL
              . ''.PHP_EOL
              . '    // }}}'.PHP_EOL
              ;
              return $text;
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
