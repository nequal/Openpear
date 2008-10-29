<?php
Rhaco::import('SvnUtil');

class OpenpearRepository extends Openpear
{
    var $allowExt = array('txt','php','css','js','pl','cgi','rb','py','phps','c');

    function browse($path='/'){
        if(empty($path)) $path = '/';
        $this->setVariable('path', $path);
        $path = sprintf('file://%s/%s%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path);
        $svn = new SvnUtil();
        
        $files = $svn->execute('list', $path);
        if(isset($files['list']['entry']['kind']) && $files['list']['entry']['kind'] == 'file'){
            // file
            if($file = explode('.', $path)){
                $ext = array_pop($file);
                if(in_array($ext, $this->allowExt)){
                    $this->setVariable('body', $svn->cmd('cat '. $path));
                }
            }
            $this->setVariable('entry', $files['entry']);
            return $this->parser('repository/detail.html');
        } else {
            // dir
            $this->setVariable('entries', $files['list']['entry']);
            return $this->parser('repository/list.html');
        }
    }
}
