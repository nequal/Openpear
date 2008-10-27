<?php
/**
 * SvnUtil
 *
 * @author  riaf <riafweb@gmail.com>
 * @license New BSD License
 * @version $Id$
 */

class SvnUtil
{
    var $svn_path;

    function SvnUtil($svn_path='/usr/bin/svn'){
        $this->svn_path = $svn_path;
    }

    /*
     * XML 形式で取得して結果を返す
     * @param string $cmd
     * @param string $args
     * @return mixed
     */
    function execute($cmd, $args){
        $xml = $this->cmd($this->svn_path. ' '. $cmd. ' --xml '. $args);
        if(SimpleTag::setof($tag, $xml)){
            return $tag->toHash();
        }
        return $xml;
    }

    function cp($src, $dst, $option=''){
        $r = $this->_cmd(sprintf("%s copy %s %s %s", $this->svn_path, $src, $dst, $option));
    }
    function export($src, $path='.', $option=''){
        $src = ArrayUtil::arrays($src);
        $src = implode(' ', $src);
        $r = $this->_cmd(sprintf("%s export %s %s %s", $option, $src, $path));
    }

    function cmd($cmd){
        Logger::debug('svn called: '. $cmd);
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

