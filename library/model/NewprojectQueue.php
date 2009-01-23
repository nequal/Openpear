<?php
Rhaco::import("model.table.NewprojectQueueTable");
Rhaco::import('tag.HtmlParser');
Rhaco::import('Gmail');
Rhaco::import('SvnUtil');
/**
 * 
 */
class NewprojectQueue extends NewprojectQueueTable{
    function createRepository(&$db){
        $package = $db->get(new Package(), new C(Q::eq(Package::columnId(), $this->package)));
        if(Variable::istype('Package', $package)){
            // create repository
            $wp = Rhaco::constant('WORKING_DIR'). '/NEWREP'. md5($package->name);
            $path = sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), $package->name);
            $svn = new SvnUtil();
            $svn->cmd(sprintf('mkdir %s -m "[Add Package] %s"', $path, $package->name));
            $svn->_cmd('rm -rf '. $wp);
            $svn->cmd(sprintf('co %s %s', $path, $wp));
            FileUtil::mkdir($wp.'/trunk');
            FileUtil::mkdir($wp.'/tags');
            FileUtil::mkdir($wp.'/branches');
            $svn->cmd('add '.$wp.'/trunk '.$wp.'/tags '.$wp.'/branches');
            $svn->cmd(sprintf('ci %s -m "[Create Base Directory] %s"', $wp, $package->name));
            $svn->_cmd('rm -rf '. $wp);
            
            if($this->mailPossible) $this->sendSccessMail($db, $package);
            return true;
        }
        return false;
    }
    function sendSccessMail(&$db, $package){
        if(!isset($_SERVER['REMOTE_ADDR'])) $_SERVER['REMOTE_ADDR'] = '208.113.174.213'; // FIXME
        $maintainer = $db->get(new Maintainer(), new C(Q::eq(Maintainer::columnId(), $this->maintainer)));
        if(Variable::istype('Maintainer', $maintainer) && !empty($maintainer->mail)){
            $parser = new HtmlParser();
            $parser->setVariable('package', $package);
            $parser->setVariable('maintainer', $maintainer);
            $mail = new Gmail(Rhaco::constant('GMAIL_ACCOUNT'), Rhaco::constant('GMAIL_PASSWORD'));
            $mail->to($maintainer->mail, $maintainer->fullname);
            $mail->subject('Welcome to OPENPEAR!');
            $mail->message($parser->read('mail/newpackage.tpl'));
        }
    }
}

?>