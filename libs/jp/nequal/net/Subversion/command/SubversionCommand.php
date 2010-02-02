<?php
class SubversionCommand extends Object
{
    static protected $__cmd_path__ = '/usr/bin/svn';
    static protected $__look_cmd_path__ = '/usr/bin/svnlook';
    static protected $_lang_ = 'ja_JP.UTF-8';
    protected $_command_ = 'help';
    protected $vars = array();
    protected $options = array();
    static protected $__vars__ = 'type=mixed{}';
    static protected $__options__ = 'type=mixed{}';

    static public function __import__(){
        self::$__cmd_path__ = module_const('cmd_path','/usr/bin/svn');
        self::$__look_cmd_path__ = module_const('look_cmd_path','/usr/bin/svnlook');
        self::$_lang_ = module_const('lang','ja_JP.UTF-8');
    }
    /**
     * @@FIXME
     */
    static public function look($command, $vars=array(), $options=array(), $dict=null){
        $cmd = isset(self::$_lang_)? sprintf('env LANG=%s ', self::$_lang_): '';
        $cmd .= self::$__look_cmd_path__;
        $cmd .= ' ';
        $cmd .= $command;
        foreach($options as $name => $value){
            if($name === $value){
                $cmd .= sprintf(' --%s', $name);
            } else {
                $cmd .= sprintf(' --%s=%s', $name, escapeshellarg($value));
            }
        }
        foreach($vars as $var){
            $cmd .= ' ';
            $cmd .= escapeshellarg($var);
        }
        return self::__exec_cmd__($cmd);
    }
    
    public function exec(){
        $this->__before_exec__();
        $ret = $this->__exec__();
        $this->__after_exec__($ret);
        return $ret;
    }
    protected function __before_exec__(){}
    protected function __after_exec__(&$ret){}
    protected function __exec__(){
        $cmd = isset(self::$_lang_)? sprintf('env LANG=%s ', self::$_lang_): '';
        $cmd .= self::$__cmd_path__;
        $cmd .= ' ';
        $cmd .= $this->_command_;
        foreach($this->options() as $name => $value){
            if($name === $value){
                $cmd .= sprintf(' --%s', $name);
            } else {
                $cmd .= sprintf(' --%s=%s', $name, escapeshellarg($value));
            }
        }
        foreach($this->vars() as $var){
            $cmd .= ' ';
            $cmd .= escapeshellarg($var);
        }
        return self::__exec_cmd__($cmd);
    }
    static protected function __exec_cmd__($cmd){
        Log::debug('called cmd: '. $cmd);
        ob_start();
        passthru($cmd);
        return ob_get_clean();
    }
}
