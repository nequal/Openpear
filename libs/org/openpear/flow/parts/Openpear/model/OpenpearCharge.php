<?php
import('org.rhaco.storage.db.Dao');

class OpenpearCharge extends Dao
{
    protected $package_id;
    protected $maintainer_id;
    protected $role;
    
    static protected $__package_id__ = 'type=number,require=true,primary=true';
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    static protected $__role__ = 'type=choice(lead,developer,contributor,helper),require=true';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPacakge,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function __after_save__(){
        $maintainers = C(OpenpearMaintainer)->find_all();
        $packages = C(OpenpearPackage)->find_all();
        $template = new Template();
        $template->vars('maintainers', $maintainers);
        $template->vars('packages', $packages);
        File::write(module_const('svn_access_file'), $template->read('files/access.txt'));
    }
    
    protected function __get_package__(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
            }catch(Exception $e){}
        }
        return $this->package;
    }
    protected function __get_maintainer__(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
            }catch(Exception $e){}
        }
        return $this->maintainer;
    }
}
