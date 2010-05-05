<?php
class PackageProjectorConfigFile extends PackageProjectorConfigExtra
{
    protected $filename;
    protected $commandscript;
    protected $ignore;
    protected $platform;
    protected $install;
    protected $role;
    static protected $__filename__ = 'type=string';
    static protected $__commandscript__ = 'type=string';
    static protected $__role__ = 'type=choice(php,data,doc,test,script,src)';
    
    protected $_special_section_ = 'filename';
    
    protected function __section__(){
        return 'file://'. $this->filename;
    }
    protected function __str__(){
        return $filename;
    }
}