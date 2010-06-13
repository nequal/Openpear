<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');
import('org.openpear.pear.PackageProjector');

class OpenpearRelease extends Dao implements AtomInterface
{
    protected $id; # リリースID
    protected $package_id; # パッケージID
    protected $maintainer_id; # メンテナID
    protected $version; # バージョン
    protected $version_stab;
    protected $notes;
    protected $settings;
    protected $created; # 作成日時
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__  = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__version__ = 'type=string,require=true';
    static protected $__version_stab__ = 'type=choice(stable,beta,alpha),require=true';
    static protected $__notes__ = 'type=text';
    static protected $__settings__ = 'type=text';
    static protected $__created__ = 'type=timestamp';
    
    private $package;
    private $maintainer;
    private $fm_settings;
    
    public function filename() {
        return $this->package_name(). '-'. $this->version(). '.tgz';
    }
    
    protected function __init__(){
        $this->version = '1.0.0';
        $this->version_stab = 'stable';
        $this->created = time();
    }
    protected function __fm_version__(){
        if(is_null($this->id)) return 'No Release';
        if($this->version_stab === 'stable') return $this->version();
        return sprintf('%s (%s)', $this->version, $this->version_stab);
    }
    protected function __fm_settings__(){
        if (is_null($this->fm_settings)) {
            $settings = new PackageProjectorConfig();
            $settings->parse_ini_string($this->settings);
            $this->fm_settings = $settings;
        }
        return $this->fm_settings;
    }
    // DBにつながないというエコ
    public function package_name() {
        $fm_settings = $this->fm_settings();
        return $fm_settings->is_package_package_name()?
            $fm_settings->package_package_name(): $this->package()->name();
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
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_id()
     */
    public function atom_id(){
        return $this->id();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_title()
     */
    public function atom_title(){
        return $this->package_name();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_published()
     */
    public function atom_published(){
        return $this->created();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_updated()
     */
    public function atom_updated(){
        return $this->created();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_issued()
     */
    public function atom_issued(){
        return $this->created();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_content()
     */
    public function atom_content(){
    	return Gettext::trans('{1} released new version.', $this->package_name());
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_summary()
     */
    public function atom_summary(){
        return Gettext::trans('{1} released new version.', $this->package_name());
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_author()
     */
    public function atom_author(){
        return str($this->maintainer());
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_href()
     */
    public function atom_href(){
        return url('package/'). $this->package_name();
    }
}
