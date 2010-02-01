<?php
class PackageProjectorConfig extends Object
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
    protected $package_summary_file;
    protected $package_description_file;
    protected $package_notes_file;
    protected $role;
    protected $version_release_ver = '1.0.0';
    protected $version_release_stab = 'stable';
    protected $version_api_ver;
    protected $version_api_stab;
    protected $version_php_min = '5.1.6';
    protected $version_pear_min = '1.8.2';
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
    static protected $__package_summary_file__ = 'type=string';
    static protected $__package_description_file__ = 'type=string';
    static protected $__package_notes_file__ = 'type=string';
    static protected $__role__ = 'type=string{}';
    static protected $__version_release_ver__ = 'type=string,require=true';
    static protected $__version_release_stab__ = 'type=choice(stable,beta,alpha),require=true';
    static protected $__version_api_ver__ = 'type=string';
    static protected $__version_api_stab__ = 'type=choice(stable,beta,alpha)';
    static protected $__version_php_min__ = 'type=string';
    static protected $__version_pear_min__ = 'type=string';
    static protected $__license_name__ = 'type=string';
    static protected $__license_uri__ = 'type=string';
    static protected $__maintainer__ = 'type=PackageProjectorConfigMaintainer[]';
    static protected $__file__ = 'type=PackageProjectorConfigFile[]';
    static protected $__dep__ = 'type=PackageProjectorConfigDep[]';
    static protected $__installer__ = 'type=PackageProjectorConfigInstaller[]';
    
    static private $_keys_ = array(
        'maintainer' => 'handlename',
        'file' => 'filename',
        'dep' => 'package_name',
        'installer' => 'group_name',
        'dep_o' => 'channel_other',
    );
    
    /**
     * リクエストから一括で登録するとかにつかう。結構決め打ち
     */
    public function set_vars(array $vars){
        $access_vars = $this->get_access_vars();
        foreach($vars as $name => $value){
            if(in_array($name, array('maintainer', 'file', 'dep'))){
                $class = 'PackageProjectorConfig'. ucfirst($name);
                foreach($value as $v){
                    $obj = new $class();
                    $obj->cp($v);
                    $this->{$name}($obj);
                }
            } else if($name === 'installer'){
                // #pass むりぽw
            } else {
                if(array_key_exists($name, $access_vars)) $this->{$name}($value);
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
                    $ret .= sprintf("%s = \"%s\"\n",
                        $key, str_replace(array("\n", '"'), array("", '\"'), Text::uld($val)));
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
                if(!isset($ret[$section]) && !self::special_section($section)) $ret[$section] = array();
                foreach($val as $k => $obj){
                    if($obj instanceof Object){
                        $hash = $obj->hash();
                        if(!empty($hash)) $ret[$obj->section()] = $hash;
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
    protected function __get_version_api_ver__(){
        return empty($this->version_api_ver)? $this->version_release_ver(): $this->version_api_ver;
    }
    protected function __get_version_api_stab__(){
        return empty($this->version_api_stab)? $this->version_release_stab(): $this->version_api_stab;
    }
    protected function __verify_version_release_ver__(){    
        if(!$this->is_version($this->version_release_ver)){
            Exceptions::add(new OpenpearException(), 'version_release_ver');
        }
    }
    protected function __verify_version_api_ver__(){
        if(!empty($this->version_api_ver) && !$this->is_version($this->version_api_ver)){
            Exceptions::add(new OpenpearException(), 'version_api_ver');
        }
    }
    protected function __verify_package_baseinstalldir__(){
        if(!preg_match('@^[A-Za-z0-9\.\/\_\-]+$@', $this->package_baseinstalldir)){
            Exceptions::add(new OpenpearException(), 'package_baseinstalldir');
        }
    }
    static public function special_section($section){
        return isset(self::$_keys_[$section]);
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
                $installer = new PackageProjectorConfigInstaller();
                foreach($v as $key => $val){
                    if($key === 'instructions'){
                        $installer->instructions($val);
                    } else {
                        list($param, $opt) = explode('.', $key);
                        $installer->{$opt}($param, $val);
                        $installer->params($param);
                    }
                }
                $installer->group_name($index);
                $this->installer($installer);
            } else if(in_array($parent_key, array('maintainer', 'file', 'dep'))) {
                $class = 'PackageProjectorConfig'. ucfirst($parent_key);
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
    }
}
