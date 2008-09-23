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
            'channel' => 'pear.riaf.jp',
            'summary_file' => 'desc.txt',
            'description_file' => 'desc.txt',
            'notes_file' => 'notes.txt',
        ),
        'version' => array(
            'release_ver' => '0.1.0',
            'release_stab' => 'stable',
            'api_ver' => '0.1.2',
            'api_stab' => 'stable',
            'php_min' => '4.3.3',
            'pear_min' => '1.4.0',
        ),
        'license' => array(
            'name' => 'New BSD Licence',
            'uri' => 'http://creativecommons.org/licenses/BSD/',
        ),
    );
    var $description = '';
    var $notes = '';

    function Release($name){
        $this->variables['package']['package_name'] = $name;
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
        $work_path = Rhaco::constant('WORKING_DIR');
        chdir($work_path);

        $svn = SvnUtil();
        $svn->_cmd('rm -rf '. $work_path);
        FileUtil::cp(Rhaco::resource('skelton'), $work_path);
        $this->writeINI($work_path.'/build.conf');
        file_put_contents($work_path.'/desc.txt', $this->description);
        if(empty($this->notes)) $this->notes = $svn->cmd(sprintf("log file://%s/%s/%s", Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path));
        file_put_contents($work_path.'/notes.txt', $this->notes);

        $svn->cmd(sprintf('export file://%s/%s/%s %s/src', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path, $work_path));

        ob_start();
        system('cd '.$work_path);
        system($work_path.'/build');
        $ret = ob_get_clean();

        $files = FileUtil::ls($work_path.'/release');
        $ret = false;
        foreach($files as $file){
            if($file->getExtension() == '.tgz'){
                $ret = $this->registerPackage($file->getFullname());
                break;
            }
        }
        // タグ打ちとかする？
        return $ret;
    }
    function registerPackage($packageFile){
        $cfg = include(Rhaco::path('channel.config.php'));
        $server = new PEAR_Server2($cfg);
        try {
            $package = $server->core->generatePackage($packageFile);
            if(false === $this->core->validatePackage($package)){
                return false;
            }
            $this->core->copyPackage($packageFile, $package->getFileName());
            $this->backend->insertPackage($package);
            $this->backend->insertCategory($package->getCategoryObject());
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}

?>
