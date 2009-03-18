<?php
Rhaco::import('util.SvnUtil');
Rhaco::import('io.Cache');
Rhaco::import('model.RepositoryLog');
Rhaco::import('view.ViewBase');

class RepositoryView extends ViewBase
{
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

    /**
     * @todo revision
     */
    function browse($path='/'){
        if(empty($path) || preg_match('/\s/', $path)) $path = '/';
        $this->setVariable('path', $path);
        $path = sprintf('file://%s/%s', Rhaco::constant('SVN_PATH'), preg_replace('/\.+/', '.', $path));
        $revision = $this->getVariable('rev', 'HEAD');
        
        // エントリ情報を取得
        $info = SvnUtil::info($path, array('revision' => $revision));
        if(!isset($info['entry']['kind'])){
            // 無かったら駄目
            return $this->_notFound();
        }
        // ファイル情報一覧
        $entries = SvnUtil::ls($path, array('revision' => $revision));
        switch($info['entry']['kind']){
            case 'file':
                $file = $info['entry'];
                // TODO: 外に出す
                $allowExt = array('txt','php','css','js','pl','cgi','rb','py','phps','c');
                if($file = explode('.', $path)){
                    $ext = array_pop($file);
                    if(in_array($ext, $allowExt)){
                        $this->setVariable('body', SvnUtil::cat($path, array('revision' => $file['commit']['revision'])));
                    }
                }
                $file['log'] = $this->_getLog($path, $file['commit']['revision']);
                $this->setVariable('entry', $file);
                return $this->parser('repository/detail.html');
                
            case 'dir':
                $files = array();
                if(isset($entries['entry'])){
                    $files = isset($entries['entry']['kind'])? array($entries['entry']): $entries['entry'];
                }
                foreach($files as &$file){
                    $file['log'] = $this->_getLog($path. '/'. $file['name'], $file['commit']['revision']);
                }
                $this->setVariable('rev', $info['entry']['commit']['revision']);
                $this->setVariable('log', $this->_getLog($path, $info['entry']['commit']['revision']));
                $this->setVariable('entries', $files);
                return $this->parser('repository/list.html');
                
            default:
                return $this->_notFound();
        }
    }
    function _getLog($path, $rev='HEAD'){
        $key = is_numeric($rev) ? $rev : array($path, $rev);
        $log = '';
        if(Cache::isExpiry($key, 3600*24*30*12)){
            $logs = SvnUtil::log($path, 'r='. $rev);
            $log = isset($logs['logentry']['msg']) ? $logs['logentry']['msg'] : '';
            Cache::set($key, $log);
        } else {
            $log = Cache::get($key);
        }
        return $log;
    }
}
