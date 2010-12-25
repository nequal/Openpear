<?php
/**
 * PackageProjectorConfigInstaller
 *
 * @var string $group_name
 * @var string $instructions
 * @var string[] $params
 * @var string{} $prompt
 * @var string{} $type
 * @var string{} $default
 */
class PackageProjectorConfigInstaller extends PackageProjectorConfigExtra
{
    protected $group_name;
    protected $instructions;
    protected $params = array();
    protected $prompt;
    protected $type;
    protected $default;
    
    protected $_special_section_ = 'groupname';
    
    protected function __hash__(){
        $ret = array();
        if($this->is_instructions()){
            $ret['instructions'] = $this->instructions();
        }
        foreach($this->params() as $param){
            if($this->is_prompt($param)){
                $ret[$param. '.prompt'] = $this->in_prompt($param);
            }
            if($this->is_type($param)){
                $ret[$param. '.type'] = $this->in_type($param);
            }
            if($this->is_default($param)){
                $ret[$param. '.default'] = $this->in_default($param);
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
