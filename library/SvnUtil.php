<?php
/**
 * SvnUtil
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */
Rhaco::import('lang.ArrayUtil');
Rhaco::constant('SVN_CMD_PATH', '/usr/bin/svn');

class SvnUtil
{
    /**
     * svn export
     * ディレクトリツリーのエクスポート
     */
    function export($src, $path='.', $options=null){
        $ret = SvnUtil::_execSVN('export',
            SvnUtil::_buildOption($options, array('revision')). $src. ' '. $path);
        return (strpos($ret, 'Export complete.') !== false || strpos($ret, 'Exported revision') !== false);
    }
    /**
     * svn import
     * バージョン管理されていないファイルやツリーをリポジトリにコミット
     */
    function import($path='.', $url, $options=null){
        $ret = SvnUtil::_execSVN('import',
            SvnUtil::_buildOption($options, array('message', 'm')). $path. ' '. $url);
        return strpos($ret, 'Committed revision') !== false;
    }
    
    function _exec($cmd){
        ob_start();
        Logger::deep_debug(sprintf('[%s::_exec] %s', __CLASS__, $cmd));
        passthru($cmd);
        return ob_get_clean();
    }
    function _execSVN($method, $arg){
        return SvnUtil::_exec(sprintf('%s %s %s',
            Rhaco::constant('SVN_CMD_PATH'),
            $method,
            $arg)
        );
    }
    function _buildOption($options, $keys=array()){
        $options = is_null($options)? array(): ArrayUtil::dict($options, $keys, false);
        $opt = '';
        foreach(ArrayUtil::arrays($options) as $k => $v){
            if(strlen($k) === 1 && ctype_alnum($k)){
                $ret .= sprintf(' -%s %s ', $k, escapeshellarg($v));
            } else if(preg_match('^[a-z0-9\-]+$', $k)){
                $ret .= sprintf(' --%s %s ', $k, escapeshellarg($v));
            }
        }
        return $opt;
    }

    /*
     * XML 形式で取得して結果を返す
     * @param string $cmd
     * @param string $args
     * @return mixed
     */
    function execute($cmd, $args){
        $xml = $this->cmd($cmd. ' --xml '. $args);
        if(SimpleTag::setof($tag, $xml)){
            return $tag->toHash();
        }
        return $xml;
    }

    function cmd($cmd){
        Logger::deep_debug('svn called: '. $cmd);
        $r = $this->_cmd(sprintf('%s %s', $this->svn_path, $cmd));
        Logger::deep_debug('svn result: '. $r);
        return $r;
    }

    function _cmd($cmd){
        ob_start();
        system($cmd);
        return ob_get_clean();
    }
}

