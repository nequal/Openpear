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
    public function package(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
            }catch(Exception $e){}
        }
        return $this->package;
    }
    public function maintainer(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
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
        return $this->package()->name();
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
    	// TODO **
        return sprintf('%s is released!', $this->package()->name());
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_summary()
     */
    public function atom_summary(){
        return sprintf('%s is released!', $this->package()->name());
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
        
    }
}