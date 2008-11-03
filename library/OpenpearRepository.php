<?php
Rhaco::import('SvnUtil');
Rhaco::import('io.Cache');
Rhaco::import('model.RepositoryLog');

class OpenpearRepository extends Openpear
{
    var $allowExt = array('txt','php','css','js','pl','cgi','rb','py','phps','c');

    function changeset($revision){
        $log = $this->dbUtil->get(new RepositoryLog(), new C(Q::eq(RepositoryLog::columnRevision(), $revision)));
        if(Variable::istype('RepositoryLog', $log)){
            $this->setVariable('package', $this->dbUtil->get(new Package($log->package)));
            $this->setVariable('author', $this->dbUtil->get(new Maintainer(), new C(Q::eq(Maintainer::columnName(), $log->author))));
            $this->setVariable('object', $log);
            $this->setVariable('changed', unserialize($log->diff));
            return $this->parser('repository/log.html');
        }
        return $this->_notFound();
    }

    function browse($path='/'){
        if(empty($path) || preg_match('/\s/', $path)) $path = '/';
        $path = sprintf('file://%s/%s%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME'), $path);
        $svn = new SvnUtil();
        
        $info = $svn->execute('info', escapeshellarg($path));
        if(!isset($info['entry']['kind'])) return $this->_notFound();
        $kind = $info['entry']['kind'];
        
        $files = $svn->execute('list', escapeshellarg($path));
        $this->setVariable('path', (isset($files['list']['path']) && !empty($files['list']['path'])) ? 
            str_replace(sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), Rhaco::constant('SVN_NAME')), '', $files['list']['path'])
            : '');
        switch($kind){
            case 'file':
                // file
                if($file = explode('.', $path)){
                    $ext = array_pop($file);
                    if(in_array($ext, $this->allowExt)){
                        $body = $svn->cmd('cat '. escapeshellarg($path));
                        $this->setVariable('body', $body);
                        if($ext == 'php'){
                            Rhaco::import('util.DocUtil');
                            $this->setVariable('doc', new DocUtil($body, $path));
                        }
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
        $key = is_numeric($rev) ? $rev : array($path, $rev);
        $log = '';
        $svn = new SvnUtil();
        if(Cache::isExpiry($key, 3600*24*30*12)){
            $logs = $svn->execute('log -r '. $rev, escapeshellarg($path));
            $log = isset($logs['logentry']['msg']) ? $logs['logentry']['msg'] : '';
            Cache::set($key, $log);
        } else {
            $log = Cache::get($key);
        }
        return $log;
    }
}
