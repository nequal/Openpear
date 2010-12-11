<?php
/**
 * PackageProjectorConfigFile
 *
 * @var string $filename
 * @var string $commandscript
 * @var choice $role @{"choices":["php","data","doc","test","script","src"]}
 */
class PackageProjectorConfigFile extends PackageProjectorConfigExtra
{
    protected $filename;
    protected $commandscript;
    protected $ignore;
    protected $platform;
    protected $install;
    protected $role;
    
    protected $_special_section_ = 'filename';
    
    protected function __section__(){
        return 'file://'. $this->filename;
    }
    protected function __str__(){
        return $filename;
    }
}
