<?php
/**
 * きもい
 */
class PearBuildconf extends Object
{
    protected $project_src_dir = 'src';
    protected $project_release_dir = 'release';
    protected $document_doc_dir = 'doc';
    protected $document_tutorial_file;
    protected $document_stylesheet_file;
    protected $package_package_name;
    protected $package_package_type = 'php';
    protected $package_baseinstalldir = '.';
    protected $package_channel = 'openpear.org';
    protected $package_summary;
    protected $package_description;
    protected $package_notes;
    protected $package_summary_file = 'summary.txt';
    protected $package_description_file = 'desc.txt';
    protected $package_notes_file = 'notes.txt';
    protected $role;
    protected $version_release_ver = '1.0.0';
    protected $version_release_stab = 'stable';
    protected $version_api_ver = '1.0.0';
    protected $version_api_stab = 'stable';
    protected $version_php_min = '5.1.6';
    protected $version_pear_min = '1.8.0';
    protected $license_name = 'PHP License 3.01';
    protected $license_uri = 'http://openpear.org/license';
    protected $maintainer = array();
    protected $file = array();
    protected $dep = array();
    protected $installer = array();
    
    static protected $__project_src_dir__ = 'type=string,set=false';
    static protected $__project_release_dir__ = 'type=string,set=false';
    static protected $__document_doc_dir__ = 'type=string';
    static protected $__document_tutorial_file__ = 'type=string';
    static protected $__document_stylesheet_file__ = 'type=string';
    static protected $__package_package_name__ = 'type=string,require=true';
    static protected $__package_package_type__ = 'type=string,set=false';
    static protected $__package_baseinstalldir__ = 'type=string,require=true';
    static protected $__package_channel__ = 'type=string,require=true,set=false';
    static protected $__package_summary__ = 'type=string';
    static protected $__package_description__ = 'type=string';
    static protected $__package_notes__ = 'type=text';
    static protected $__package_summary_file__ = 'type=string,set=false';
    static protected $__package_description_file__ = 'type=string,set=false';
    static protected $__package_notes_file__ = 'type=string,set=false';
    static protected $__role__ = 'type=string{}';
    static protected $__version_release_ver__ = 'type=string,require=true';
    static protected $__version_release_stab__ = 'type=choice(stable,beta,alpha),require=true';
    static protected $__version_api_ver__ = 'type=string';
    static protected $__version_api_stab__ = 'type=choice(stable,beta,alpha)';
    static protected $__version_php_min__ = 'type=string';
    static protected $__version_pear_min__ = 'type=string';
    static protected $__license_name__ = 'type=string';
    static protected $__license_uri__ = 'type=string';
    static protected $__maintainer__ = 'type=PearBuildconfMaintainer[]';
    static protected $__file__ = 'type=PearBuildconfFile[]';
    static protected $__dep__ = 'type=PearBuildConfDep[]';
    static protected $__installer__ = 'type=PearBuildConfInstaller[]';
    
    static private $_keys_ = array('maintainer' => 'handlename', 'file' => 'filename', 'dep' => 'package_name', 'installer' => 'group_name');
    
    /**
     * リクエストから一括で登録するとかにつかう。結構決め打ち
     */
    public function set_vars(array $vars){
        foreach($vars as $name => $value){
            if(in_array($name, array('maintainer', 'file', 'dep'))){
                $class = 'PearBuildConf'. ucfirst($name);
                foreach($value as $v){
                    $obj = new $class();
                    $obj->cp($v);
                    $this->{$name}($obj);
                }
            } else if($name === 'installer'){
                // #pass むりぽw
            } else {
                $this->{$name}($value);
            }
        }
    }
    /**
     * ini 形式で取得
     */
    public function get_ini(){
        $ret = '';
        foreach($this->hash() as $section => $values){
            if(empty($section) || empty($values)) continue;
                $ret .= sprintf("[%s]\n", $section);
            if(is_array($values)) foreach($values as $key => $val){
                if(is_string($val)){
                    $ret .= sprintf("%s = %s\n", $key, str_replace("\n", "", Text::uld($val)));
                }
            }
            $ret .= "\n";
        }
        return $ret;
    }
    public function write($filename){
        File::write($filename, $this->get_ini());
    }
    protected function __hash__(){
        $ret = array();
        $basic_vars = $this->get_access_vars();
        foreach($basic_vars as $key => $val){
            $keys = explode('_', $key, 2);
            if(count($keys) === 1 && is_array($val)){
                $section = array_shift($keys);
                if(!isset($ret[$section])) $ret[$section] = array();
                foreach($val as $k => $obj){
                    if($obj instanceof Object){
                        $ret[$obj->section()] = $obj->hash();
                    } else $ret[$section][$k] = $obj;
                }
            } else if(count($keys) === 2 && is_string($val)) {
                list($section, $name) = $keys;
                if(!isset($ret[$section])) $ret[$section] = array();
                $ret[$section][$name] = $val;
            }
        }
        return $ret;
    }
    
    private function is_version($var){
        return (bool) preg_match('/^\d+\.\d+\.\d+$/', $var);
    }
    protected function verifyVersion_release_ver(){
        if(!$this->is_version($this->version_release_ver)){
            Exceptions::add(new OpenpearException(), 'version_release_ver');
        }
    }
    protected function verifyVersion_api_ver(){
        if(!$this->is_version($this->version_api_ver)){
            Exceptions::add(new OpenpearException(), 'version_api_ver');
        }
    }
    protected function verifyPackage_baseinstalldir(){
        if(!preg_match('@^[A-Za-z0-9\.\/\_\-]+$@', $this->package_baseinstalldir)){
            Exceptions::add(new OpenpearException(), 'package_baseinstalldir');
        }
    }
    
    public function parse_ini_string($ini){
        $config = parse_ini_string($ini, true);
        if(empty($config)) return ;
        $indexes = array('maintainer' => 0, 'file' => 0, 'dep' => 0);
        foreach($config as $k => $v){
            $parent_key = $index = '';
            if(preg_match('@^(maintainer|file|dep|installer)://(.*)$@', $k, $match)){
                $parent_key = $match[1];
                $index = $match[2];
            } else {
                $parent_key = $k;
            }
            if($parent_key == 'installer'){
                list($param, $opt) = explode('.', $key);
                $installer = new PearBuildConfInstaller();
                $installer->group_name($index);
                $installer->params($param);
                $installer->{$opt}($param, $val);
                $this->installer($installer);
            } else if(in_array($parent_key, array('maintainer', 'file', 'dep'))) {
                $class = 'PearBuildConf'. ucfirst($parent_key);
                $pk = self::$_keys_[$parent_key];
                $obj = new $class();
                $obj->cp($v);
                $obj->{$pk}($index);
                $this->{$parent_key}($obj);
            } else {
                foreach((array)$v as $key => $val){
                    if($parent_key === 'role') $this->role($key, $val);
                    else $this->{$parent_key. '_'. $key}($val);
                }
            }
            if(isset($indexes[$parent_key])){
                $indexes[$parent_key]++;
            }
        }
        return $result;
    }
}

class PearBuildconfExtra extends Object
{
    protected function __hash__(){
        $ret = array();
        foreach($this->get_access_vars() as $key => $val){
            if(!in_array($key, array('handlename', 'filename', 'package_name', 'group_name'))) $ret[$key] = $val;
        }
        return $ret;
    }
    public function section(){
        return $this->__section__();
    }
    protected function __section__(){
        return null;
    }
}
class PearBuildconfMaintainer extends PearBuildconfExtra
{
    protected $handlename;
    protected $name;
    protected $mail;
    protected $role = 'lead';
    static protected $__handlename__ = 'type=string,require=true';
    static protected $__name__ = 'type=string,require=true';
    static protected $__mail__ = 'type=email';
    static protected $__role__ = 'type=choice(lead,developer,contributor,helper)';
    
    protected function __section__(){
        return 'maintainer://'. $this->handlename;
    }
    protected function __str__(){
        return $handlename;
    }
}
class PearBuildconfFile extends PearBuildconfExtra
{
    protected $filename;
    protected $commandscript;
    protected $ignore;
    protected $platform;
    protected $install;
    protected $role;
    static protected $__filename__ = 'type=string';
    static protected $__commandscript__ = 'type=string';
    static protected $__role__ = 'type=choice(php,data,doc,test,script,src)';
    
    protected function __section__(){
        return 'file://'. $this->filename;
    }
    protected function __str__(){
        return $filename;
    }
}
class PearBuildConfDep extends PearBuildconfExtra
{
    protected $package_name;
    protected $type = 'required';
    protected $channel = 'openpear.org';
    protected $min;
    protected $max;
    static protected $__package_name__ = 'type=string';
    static protected $__type__ = 'type=choice(required,optional)';
    static protected $__channel__ = 'type=string';
    
    protected function __section__(){
        return 'dep://'. $this->package_name;
    }
    protected function __str__(){
        return $package_name;
    }
}
class PearBuildConfInstaller extends PearBuildconfExtra
{
    protected $group_name;
    protected $instructions;
    protected $params = array();
    protected $prompt;
    protected $type;
    protected $default;
    static protected $__group_name__ = 'type=string';
    static protected $__instructions__ = 'type=string';
    static protected $__params__ = 'type=string[]';
    static protected $__prompt__ = 'type=string{}';
    static protected $__type__ = 'type=string{}';
    static protected $__default__ = 'type=string{}';
    
    protected function __hash__(){
        $ret = array();
        foreach($this->params() as $param){
            if($this->isPrompt($param)){
                $ret[$param. '.prompt'] = $this->inPrompt($param);
            }
            if($this->isType($param)){
                $ret[$param. '.type'] = $this->inType($param);
            }
            if($this->isDefault($param)){
                $ret[$param. '.default'] = $this->inDefault($param);
            }
        }
        return $ret;
    }
    protected function __section__(){
        return 'installer://'. $this->group_name;
    }
    protected function __str__(){
        return $this->group_name;
    }
}
