<?php
Rhaco::import('SvnUtil');

class OpenpearRepository extends Openpear
{
    var $allowExt = array('txt','php','css','js','pl','cgi','rb','py','phps','c');

    function browse($path='/'){
        if(empty($path)) $path = '/';
        $path = sprintf('file://%s/%s%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path);
        $svn = new SvnUtil();
        
        $info = $svn->execute('info', $path);
        $kind = $info['entry']['kind'];
        
        $files = $svn->execute('list', $path);
        $this->setVariable('path', (isset($files['list']['path']) && !empty($files['list']['path'])) ? 
            str_replace(sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME')), '', $files['list']['path'])
            : '');
        switch($kind){
            case 'file':
                // file
                if($file = explode('.', $path)){
                    $ext = array_pop($file);
                    if(in_array($ext, $this->allowExt)){
                        $this->setVariable('body', $svn->cmd('cat '. $path));
                    }
                }
                $this->setVariable('entry', $files['list']['entry']);
                return $this->parser('repository/detail.html');

            case 'dir':
                // dir
                $entries = isset($files['list']['entry']['kind']) ? array($files['list']['entry']) : $files['list']['entry'];
                $this->setVariable('entries', $entries);
                return $this->parser('repository/list.html');

            default:
                return $this->_notFound();
        }
    }
}
