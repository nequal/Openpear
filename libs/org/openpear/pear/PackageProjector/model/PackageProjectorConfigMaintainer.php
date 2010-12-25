<?php
/**
 * PackageProjectorConfigMaintainer
 *
 * @var string $handlename @{"require":true}
 * @var string $name @{"require":true}
 * @var email $mail
 * @var choice $role @{"choices":["lead","developer","contributor","helper"]}
 */
class PackageProjectorConfigMaintainer extends PackageProjectorConfigExtra
{
    protected $handlename;
    protected $name;
    protected $mail;
    protected $role = 'lead';
    
    protected $_special_section_ = 'handlename';
    
    public function set_charge(OpenpearCharge $charge){
        $this->handlename($charge->maintainer()->name());
        $this->name(str($charge->maintainer()));
        $this->mail($charge->maintainer()->mail());
        $this->role($charge->role());
        return $this;
    }
    protected function __section__(){
        return 'maintainer://'. $this->handlename;
    }
    protected function __str__(){
        return $handlename;
    }
}
