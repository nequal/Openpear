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
 * @since      Class available since Release 1.0.0
 */

class PEAR_PackageProjector_Document {
    private $_dir = null;
    private $_pm = array();
    private $_release_version = '';
    private $_src_dir = '';
    private $_hatena_config = array('headerlevel' => 3);
    
    public function __construct($dir)
    {
        require_once 'HatenaSyntax.php';
        $this->_dir = $dir;
    }
    
    public function build()
    {
        $handler = PEAR_PackageProjector::singleton()->getMessageHandler();
        // Create API Document
        $handler->buildMessage(5, "Update API Document files... ", true);
        ob_start();
        require_once "phpDocumentor/phpDocumentor/Setup.inc.php";
        global $argv;
        $apidoc = $this->_dir.'/'.$this->_release_version;        
        $old_argv = $argv;
        $argv = array('-p','-t', $apidoc, '-d', $this->_src_dir);
        $phpdoc = new phpDocumentor_setup;
        $phpdoc->readCommandLineSettings();
        $phpdoc->setupConverters();
        $phpdoc->createDocs();
        $buff = ob_get_contents();
        ob_end_clean();
        $handler->buildMessage(5, $buff, true);
        // Create Hatena Document
        $handler->buildMessage(5, "Update main document file... ", true);
        $list = glob($this->_dir."/*");
        $links = array();
        foreach($list as $num=>$filepath) {
            if(is_dir($filepath)) {
                $basename = basename($filepath);
                $links[] = '<li><a href="'.$basename.'/index.html">'.$basename.'</a></li>'.PHP_EOL;
            }
        }
        rsort($links);
        $this->pm('apilinks', implode(PHP_EOL, $links));
        file_put_contents($this->_dir.'index.html', $this->tpl());

    $argv = $old_argv;
    }
    
    public function accept(PEAR_PackageProjector_Visitor $visitor)
    {
        return $visitor->visitDocument($this);
    }
    
    public function setSrcDir($value) 
    {
        $this->_src_dir = $value;
    }
    
    public function setReleaseVersion($value) 
    {
        $this->_release_version = $value;
    }
    
    public function setTitle($value) 
    {
        $this->pm('title', $value);
    }
    
    public function setStylesheet($filepath) 
    {
        if (preg_match("/^@https?\:\/\//i", $filepath)) {
            $buff = file_get_contents(substr($filepath,1));
            $info = parse_url($filepath);
            $name = basename($info['path']);
            if ($name) {
				if (!is_dir($this->_dir)) {
					mkdir($this->_dir, 0755);
				}
                file_put_contents($this->_dir.'/'.basename($info['path']), $buff);
                $filepath = basename($info['path']);
            } else {
                $filepath = substr($filepath,1);
            }
        }
        $value = '<link rel="stylesheet" href="'.$filepath.'" type="text/css" media="all">';
        $this->pm('stylesheet', $value);
    }
    
    public function setDescription($value) 
    {
        $this->pm('description', HatenaSyntax::render($value, $this->_hatena_config));
    }
    
    public function setTutorial($value) 
    {
        $this->pm('tutorial', HatenaSyntax::render($value, $this->_hatena_config));
    }
    
    public function setChangelog($value) 
    {
        $this->pm('changelog', HatenaSyntax::render($value, $this->_hatena_config));
    }

    private function pm($key, $val) {
        $this->_pm['[[:'.$key.':]]'] = $val;
    }
    
    public function tpl($type='default') {
        $func = '_tpl_'.$type;
        return strtr($this->$func(), $this->_pm);
    }
    
    private function _tpl_default()
    {
        $buff = <<<EOF
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>[[:title:]]</title>
[[:stylesheet:]]
</head>
<body>
<h1>[[:title:]]</h1>
<div class="hatena-body">
<div class="main">
<div id="days">

<div class="day">
<h2><a name="description">Description</a></h2>
<div class="body">
[[:description:]]
</div>
</div>

<div class="day">
<h2><a name="tutorial">Tutorial</a></h2>
<div class="body">
[[:tutorial:]]
</div>
</div>

<div class="day">
<h2><a name="changelog">Change Log</a></h2>
<div class="body">
[[:changelog:]]
</div>
</div>

</div>
</div>
<div class="sidebar">
    <div class="hatena-module hatena-module-sectioncategory">
    <div class="hatena-moduletitle">Index</div>
    <div class="hatena-modulebody">
    <ul class="hatena-sectioncategory">
    <li><a href="#description">Description</a></li>
    <li><a href="#tutorial">Tutorial</a></li>
    <li><a href="#changelog">Change Log</a></li>
    </ul>
    </div>
    </div>
    <div class="hatena-module hatena-module-sectioncategory">
    <div class="hatena-moduletitle">API Document</div>
    <div class="hatena-modulebody">
    <ul class="hatena-sectioncategory">
    [[:apilinks:]]
    </ul>
    </div>
    </div>
</div>
</div>
</body>
</html>
EOF;
        return $buff;
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
