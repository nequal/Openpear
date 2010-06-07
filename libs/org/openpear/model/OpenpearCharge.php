<?php
import('org.rhaco.storage.db.Dao');

class OpenpearCharge extends Dao
{
    const CACHE_TIMEOUT = 3600;
    
    protected $package_id;
    protected $maintainer_id;
    protected $role;
    
    static protected $__package_id__ = 'type=number,require=true,primary=true';
    static protected $__maintainer_id__ = 'type=number,require=true,primary=true';
    static protected $__role__ = 'type=choice(lead,developer,contributor,helper),require=true';
    
    private $package;
    private $maintainer;
    
    static public function packages(OpenpearMaintainer $maintainer){
        $store_key = array('charges_maintainer', $maintainer->id());
        if (Store::has($store_key, self::CACHE_TIMEOUT)) {
            $packages = Store::get($store_key);
        } else {
            try {
                $packages = array();
                $charges = C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $maintainer->id()));
                foreach($charges as $charge){
                    $packages[] = $charge->package();
                }
            } catch(Exception $e) {
                $packages = array();
            }
            Store::set($store_key, $packages, self::CACHE_TIMEOUT);
        }
        return $packages;
    }
    
    protected function __after_save__(){
        $maintainers = C(OpenpearMaintainer)->find_all();
        $packages = C(OpenpearPackage)->find_all();
        $template = new Template();
        $template->vars('maintainers', $maintainers);
        $template->vars('packages', $packages);
        File::write(OpenpearConfig::svn_access_file(), $template->read('files/access.txt'));
    }
    
    public function package(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = OpenpearPackage::get_package($this->package_id());
            }catch(Exception $e){}
        }
        return $this->package;
    }
    public function maintainer(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = OpenpearMaintainer::get_maintainer($this->maintainer_id());
            }catch(Exception $e){}
        }
        return $this->maintainer;
    }
}
