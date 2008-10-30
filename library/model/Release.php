<?php
require_once 'PEAR/Server2.php';
Rhaco::import('SvnUtil');

class Release
{
    var $variables = array(
        'project' => array(
            'src_dir' => 'src',
            'release_dir' => 'release',
        ),
        'package' => array(
            'package_name' => null,
            'package_type' => 'php',
            'baseinstalldir' => '/',
            'channel' => 'openpear.org',
            'summary_file' => 'desc.txt',
            'description_file' => 'desc.txt',
            'notes_file' => 'notes.txt',
        ),
        'role' => array(
            'sh' => 'script',
        ),
        'version' => array(
            'release_ver' => '0.1.0',
            'release_stab' => 'stable',
            'api_ver' => '0.1.0',
            'api_stab' => 'stable',
            'php_min' => '4.3.3',
            'pear_min' => '1.4.0',
        ),
        'license' => array(
            'name' => 'New BSD Licence',
            'uri' => 'http://creativecommons.org/licenses/BSD/',
        ),
    );
    var $packageName;
    var $description = '';
    var $notes = '';
    var $buildLog = '';

    function Release($name, $baseinstalldir='/'){
        $this->variables['package']['package_name'] = $this->packageName = $name;
        $this->variables['package']['baseinstalldir'] = $baseinstalldir;
    }

    /**
     * 値をセットする
     * @param string $cat   カテゴリ
     * @param string $name  パラメタ名
     * @param string $value 値
     * @return boolean
     */
    function set($cat, $name, $value){
        if(!isset($this->variables[$cat])) $this->variables[$cat] = array();
        return $this->variables[$cat][$name] = $value;
    }

    function get(){
        $results = array('build_path' => 'trunk');
        foreach($this->variables as $cat => $variables){
            foreach($variables as $name => $value){
                $results[sprintf('%s___l___%s', $cat, $name)] = $value;
            }
        }
        return $results;
    }

    function setVersion($num, $stab='stable'){
        $this->variables['version']['release_ver'] = $num;
        $this->variables['version']['release_stab'] = $stab;
        $this->variables['version']['api_ver'] = $num;
        $this->variables['version']['api_stab'] = $stab;
    }
    function setLicense($name, $uri){
        $this->variables['license'] = array(
            'name' => $name,
            'uri' => $uri,
        );
    }
    function setMin($php_min='4.3.3', $pear_min='1.4.0'){
        $this->variables['version']['php_min'] = $php_min;
        $this->variables['version']['pear_min'] = $pear_min;
    }
    function addMaintainer($name, $fullname=null, $mail=null, $role='lead'){
        $maintainer = array();
        $maintainer['name'] = empty($fullname) ? $name : $fullname;
        if(!empty($mail)) $maintainer['email'] = $mail;
        $maintainer['role'] = $role;
        $this->variables['maintainer://'.$name] = $maintainer;
    }
    function writeINI($filename){
        $data = '';
        foreach($this->variables as $key => $val){
            $data .= sprintf("[%s]\n", $key);
            foreach($val as $k => $v){
                $data .= sprintf("%s = %s\n", $k, $v);
            }
            $data .= "\n";
        }
        file_put_contents($filename, $data);
    }

    function build($path){
        $this->verify();
        $work_path = Rhaco::constant('WORKING_DIR'). '/'. md5($path);

        $svn = new SvnUtil();
        if(!is_dir(Rhaco::constant('WORKING_DIR'))) $svn->_cmd('mkdir -p '. Rhaco::constant('WORKING_DIR'));
        if(file_exists($work_path))
            $svn->_cmd('rm -rf '. $work_path);
        FileUtil::cp(Rhaco::resource('skelton'), $work_path);
        chdir($work_path);
        $this->writeINI($work_path.'/build.conf');
        file_put_contents($work_path.'/desc.txt', $this->description);
        if(empty($this->notes)) $this->notes = $svn->cmd(sprintf("log file://%s/%s/%s", Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path));
        file_put_contents($work_path.'/notes.txt', $this->notes);

        $svn->cmd(sprintf('export file://%s/%s/%s %s/src', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path, $work_path));

        ob_start();
        system('cd '.$work_path);
        system('chmod a+x '. $work_path. '/build');
        system($work_path.'/build');
        $this->buildLog = ob_get_clean();

        $files = FileUtil::ls($work_path. '/release');
        $ret = false;
        foreach($files as $file){
            if($file->getExtension() == '.tgz'){
                $ret = $this->registerPackage($file->getFullname());
                break;
            }
        }
        // タグ打ちとかする？
        $svn->cmd(sprintf('copy file://%s/%s/%s file://%s/%s/%s/tags/%s-%s -m "%s"',
            Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path,
            Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $this->packageName, $this->variables['version']['release_ver'], $this->variables['version']['release_stab'],
            '[Add Tag:Release] '. $this->packageName
        ));
        return $ret;
    }
    function registerPackage($packageFile){
        $cfg = include(Rhaco::path('channel.config.php'));
        $server = new PEAR_Server2($cfg);
        try {
            $package = $server->core->generatePackage($packageFile);
            if(false === $server->core->validatePackage($package)){
                return false;
            }
            $server->core->copyPackage($packageFile, $package->getFileName());
            $server->backend->insertPackage($package);
            $server->backend->insertCategory($package->getCategoryObject());
        } catch (Exception $e) {
            Logger::error($e->getMessage());
            return false;
        }
        return true;
    }
    function verify(){
        $this->variables['package']['channel'] = Rhaco::constant('CHANNEL', 'openpear.org');
        $this->variables['project']['src_dir'] = 'src';
        $this->variables['project']['release_dir'] = 'release';
    }
}

?>
