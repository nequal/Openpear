<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');
import('org.openpear.pear.PackageProjector');

/**
 * Release
 *
 * @var serial $id
 * @var integer $maintainer_id @{"require":true}
 * @var string $version @{"require":true}
 * @var choice $version_stab @{"require":true,"choices":["stable","beta","alpha"]}
 * @var text $notes
 * @var text $settings
 * @var timestamp $created
 */
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

    protected function __after_create__($commit) {
        $timeline = new OpenpearTimeline();
        $timeline->subject(sprintf('<a href="%s">%s</a> <span class="hl">released</span> <a href="%s">%s %s</a>',
            url('maintainer/'. $this->author()->name()),
            $this->author()->name(),
            url('package/'. $this->name()),
            $this->name(),
            $this->fm_version()
        ));
        $timeline->description(sprintf('Download: <a href="%s">%s</a>.<pre>pear install openpear/%s</pre>',
            url("package/{$this->package_name()}/downloads#{$this->id()}"),
            $this->fm_version(),
            $this->package()->installName()
        ));
        $timeline->type('release');
        $timeline->package_id($this->package_id());
        $timeline->maintainer_id($this->maintainer_id());
        $timeline->save();
    }

    protected function __fm_version__(){
        if(is_null($this->id)) return 'No Release';
        if($this->version_stab === 'stable') return $this->version();
        return sprintf('%s (%s)', $this->version, $this->version_stab);
    }
    protected function __fm_default_version__(){
        if(is_null($this->id)) return '1.0.0';
        return $this->version();
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
        return implode(' ', array($this->package_name(), $this->fm_version()));
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
    	return $this->notes();
    }
    /**
     * @see vendors/org/rhaco/net/xml/Atom/AtomInterface#atom_summary()
     */
    public function atom_summary(){
        return $this->notes();
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
