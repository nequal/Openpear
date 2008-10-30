<?php
Rhaco::import('SvnUtil');
Rhaco::import('io.Cache');

class OpenpearRepository extends Openpear
{
    var $allowExt = array('txt','php','css','js','pl','cgi','rb','py','phps','c');

    function browse($path='/'){
        if(empty($path) || preg_match('/\s/', $path)) $path = '/';
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
                $files['list']['entry']['log'] = $this->_getLog($path, $files['list']['entry']['commit']['revision']);
                $this->setVariable('entry', $files['list']['entry']);
                return $this->parser('repository/detail.html');

            case 'dir':
                // dir
                $entries = array();
                if(isset($files['list']['entry']))
                    $entries = isset($files['list']['entry']['kind']) ? array($files['list']['entry']) : $files['list']['entry'];
                foreach($entries as &$entry){
                    $entry['log'] = $this->_getLog(
                        $path. '/'. $entry['name'],
                        $entry['commit']['revision']
                    );
                }
                $this->setVariable('rev', $info['entry']['commit']['revision']);
                $this->setVariable('log', $this->_getLog($path, $info['entry']['commit']['revision']));
                $this->setVariable('entries', $entries);
                return $this->parser('repository/list.html');

            default:
                return $this->_notFound();
        }
    }
    function _getLog($path, $rev='HEAD'){
        $key = array($path, $rev);
        $log = '';
        $svn = new SvnUtil();
        if(Cache::isExpiry($key, 3600*24*30*12)){
            $logs = $svn->execute('log -r '. $rev, $path);
            $log = isset($logs['logentry']['msg']) ? $logs['logentry']['msg'] : '';
            Cache::set($key, $log);
        } else {
            $log = Cache::get($key);
        }
        return $log;
    }
}
