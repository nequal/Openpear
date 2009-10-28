<?php
class PackageProjectorConfigMaintainer extends PackageProjectorConfigExtra
{
    protected $handlename;
    protected $name;
    protected $mail;
    protected $role = 'lead';
    static protected $__handlename__ = 'type=string,require=true';
    static protected $__name__ = 'type=string,require=true';
    static protected $__mail__ = 'type=email';
    static protected $__role__ = 'type=choice(lead,developer,contributor,helper)';
    
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
