<?php
class PackageProjectorConfigDep extends PackageProjectorConfigExtra
{
    protected $package_name;
    protected $type;
    protected $channel;
    protected $channel_other;
    protected $min;
    protected $max;
    static protected $__package_name__ = 'type=string';
    static protected $__type__ = 'type=choice(required,optional)';
    static protected $__channel__ = 'type=string';
    
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