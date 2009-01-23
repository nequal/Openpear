<?php
Rhaco::import("model.table.ReleaseQueueTable");
Rhaco::import('tag.HtmlParser');
Rhaco::import('Gmail');
Rhaco::import('SvnUtil');
/**
 * 
 */
class ReleaseQueue extends ReleaseQueueTable{
    function build(){
        require_once 'PEAR/Server2.php';
        $path = $this->buildPath;
        $work_path = Rhaco::constant('WORKING_DIR'). '/'. md5($path);

        $svn = new SvnUtil();
        if(!is_dir(Rhaco::constant('WORKING_DIR'))) $svn->_cmd('mkdir -p '. Rhaco::constant('WORKING_DIR'));
        if(file_exists($work_path))
            $svn->_cmd('rm -rf '. $work_path);
        FileUtil::cp(Rhaco::resource('skelton'), $work_path);
        chdir($work_path);
        file_put_contents($work_path. '/build.conf', $this->buildConf);
        file_put_contents($work_path.'/desc.txt', $this->description);
        if(empty($this->notes)) $this->notes = $svn->cmd(sprintf("log file://%s/%s", Rhaco::constant('SVN_PATH'), $path));
        file_put_contents($work_path.'/notes.txt', $this->notes);

        $svn->cmd(sprintf('export file://%s/%s %s/src', Rhaco::constant('SVN_PATH'), $path, $work_path));

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
                // タグ打ちとかする？
                $svn->cmd(sprintf('copy file://%s/%s file://%s/%s/tags/%s-%s -m "%s"',
                    Rhaco::constant('SVN_PATH'), $path,
                    Rhaco::constant('SVN_PATH'), $this->packageName, $this->variables['version']['release_ver'], $this->variables['version']['release_stab'],
                    '[Add Tag:Release] '. $this->packageName
                ));
                break;
            }
        }
        return $ret;
    }
    function sendSccessMail(&$db, $package, $log){
        if(!isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '208.113.174.213'; // FIXME
        $maintainer = $db->get(new Maintainer(), new C(Q::eq(Maintainer::columnId(), $this->maintainer)));
        if(Variable::istype('Maintainer', $maintainer) && !empty($maintainer->mail)){
            $parser = new HtmlParser();
            $parser->setVariable('package', $package);
            $parser->setVariable('maintainer', $maintainer);
            $parser->setVariable('releaseLog', $log);
            $mail = new Gmail(Rhaco::constant('GMAIL_ACCOUNT'), Rhaco::constant('GMAIL_PASSWORD'));
            $mail->to($maintainer->mail, $maintainer->fullname);
            $mail->subject('Package Released!');
            $mail->message($parser->read('mail/released.tpl'));
        }
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
}

?>