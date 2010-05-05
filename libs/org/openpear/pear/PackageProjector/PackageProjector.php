<?php
module('model.PackageProjectorConfig');
module('model.PackageProjectorConfigExtra');
module('model.PackageProjectorConfigMaintainer');
module('model.PackageProjectorConfigFile');
module('model.PackageProjectorConfigDep');
module('model.PackageProjectorConfigInstaller');

class PackageProjector extends Object
{
    static private $_working_dir_;
    private $_build_id_;
    
    protected function __init__(){
        $this->_build_id_ = md5(time(). mt_rand());
    }
    public function config_working_dir($working_dir){
        self::$_working_dir_ = $working_dir;
    }
    
    public function build(PackageProjectorConfig $config){
        // @todo
    }
}
