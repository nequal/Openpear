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
            SvnUtil::_buildOption($options, array('revision')). escapeshellarg($src). ' '. escapeshellarg($path));
        return (strpos($ret, 'Export complete.') !== false || strpos($ret, 'Exported revision') !== false);
    }
    /**
     * svn import
     * バージョン管理されていないファイルやツリーをリポジトリにコミット
     */
    function import($path='.', $url, $options=null){
        $ret = SvnUtil::_execSVN('import',
            SvnUtil::_buildOption($options, array('message', 'm')). escapeshellarg($path). ' '. escapeshellarg($url));
        return strpos($ret, 'Committed revision') !== false;
    }
    /**
     * svn info
     */
    function info($target='.', $options='xml'){
        $ret = SvnUtil::_execSVN('info',
            SvnUtil::_buildOption($options, array('xml', 'recursive', 'R', 'revision'), array('xml'=>null)). escapeshellarg($target));
        return SimpleTag::setof($tag, $ret, 'info')? $tag->toHash(): $ret;
    }
    /**
     * svn list
     * list メソッドを作らせてくれないphpは死ねば良い。
     */
    function ls($target='.', $options='xml'){
        $ret = SvnUtil::_execSVN('list',
            SvnUtil::_buildOption($options, array('xml', 'recursive', 'R', 'revision'), array('xml'=>null)). escapeshellarg($target));
        return SimpleTag::setof($tag, $ret, 'list')? $tag->toHash(): $ret;
    }
    /**
     * svn log
     */
    function log($path, $options='xml,r'){
        $ret = SvnUtil::_execSVN('log',
            SvnUtil::_buildOption($options, array('xml', 'limit', 'r', 'revision'), array('xml'=>null)). escapeshellarg($path));
        return SimpleTag::setof($tag, $ret, 'log')? $tag->toHash(): $ret;
    }
    /**
     * cat
     */
    function cat($target){
        return SvnUtill::_execSVN('cat', escapeshellarg($target));
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
    function _buildOption($options, $keys=array(), $extraOptions=array()){
        $options = is_null($options)? array(): ArrayUtil::dict($options, $keys, false);
        $options = array_merge($options, $extraOptions);
        $opt = '';
        foreach(ArrayUtil::arrays($options) as $k => $v){
            if(strlen($k) === 1 && ctype_alnum($k)){
                $opt .= is_null($v) ? sprintf(' -%s ', $k):
                    sprintf(' -%s %s ', $k, escapeshellarg($v));
            } else if(preg_match('/^[a-z0-9\-]+$/', $k)){
                $opt .= is_null($v) ? sprintf(' --%s ', $k):
                    sprintf(' --%s %s ', $k, escapeshellarg($v));
            }
        }
        return $opt;
    }
}

