<?php
class PackageProjectorConfigExtra extends Object
{
    protected $_special_section_;
    
    protected function __hash__(){
        $ret = array();
        foreach($this->prop_values() as $key => $val){
            if(!$this->is_special_section($key) && !empty($val)) $ret[$key] = $val;
        }
        return $ret;
    }
    public function section(){
        return $this->__section__();
    }
    protected function __section__(){
        return null;
    }
    protected function is_special_section($key){
        $special_sections = is_array($this->_special_section_)?
            $this->_special_section_: array($this->_special_section_);
        return in_array($key, $special_sections);
    }
}