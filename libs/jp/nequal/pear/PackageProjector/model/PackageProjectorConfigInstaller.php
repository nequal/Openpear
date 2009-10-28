<?php
class PackageProjectorConfigInstaller extends PackageProjectorConfigExtra
{
    protected $group_name;
    protected $instructions;
    protected $params = array();
    protected $prompt;
    protected $type;
    protected $default;
    static protected $__group_name__ = 'type=string';
    static protected $__instructions__ = 'type=string';
    static protected $__params__ = 'type=string[]';
    static protected $__prompt__ = 'type=string{}';
    static protected $__type__ = 'type=string{}';
    static protected $__default__ = 'type=string{}';
    
    protected $_special_section_ = 'groupname';
    
    protected function __hash__(){
        $ret = array();
        if($this->isInstructions()){
            $ret['instructions'] = $this->instructions();
        }
        foreach($this->params() as $param){
            if($this->isPrompt($param)){
                $ret[$param. '.prompt'] = $this->inPrompt($param);
            }
            if($this->isType($param)){
                $ret[$param. '.type'] = $this->inType($param);
            }
            if($this->isDefault($param)){
                $ret[$param. '.default'] = $this->inDefault($param);
            }
        }
        return $ret;
    }
    protected function __section__(){
        return 'installer://'. $this->group_name;
    }
    protected function __str__(){
        return $this->group_name;
    }
}