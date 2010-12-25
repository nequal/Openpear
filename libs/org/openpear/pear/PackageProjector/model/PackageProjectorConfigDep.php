<?php
/**
 * PackageProjectorConfigDep
 *
 * @var string $package_name
 * @var choice $type @{"choices":["required","optional"]}
 * @var string $channel
 */
class PackageProjectorConfigDep extends PackageProjectorConfigExtra
{
    protected $package_name;
    protected $type;
    protected $channel;
    protected $channel_other;
    protected $min;
    protected $max;
    
    protected $_special_section_ = array('package_name', 'channel_other');
    
    protected function __set_channel__($channel){
        if($channel == 99){
            $channel = $this->channel_other();
        }
        $this->channel = $channel;
    }
    protected function __section__(){
        return 'dep://'. $this->package_name;
    }
    protected function __str__(){
        return $package_name;
    }
}
