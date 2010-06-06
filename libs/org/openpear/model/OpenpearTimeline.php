<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');

class OpenpearTimeline extends Dao implements AtomInterface
{
    protected $id;
    protected $subject;
    protected $description;
    protected $type;
    protected $package_id;
    protected $maintainer_id;
    protected $created;
    static protected $__id__ = 'type=serial';
    static protected $__subject__ = 'type=string,require=true';
    static protected $__description__ = 'type=text,require=true';
    static protected $__type__ = 'type=choice(release,changeset,user_activities,package_setting,favorite)';
    static protected $__package_id__ = 'type=number';
    static protected $__maintainer_id__ = 'type=number';
    static protected $__created__ = 'type=timestamp';
    
    private $package;
    private $maintainer;
    
    protected function __init__(){
        $this->created = time();
    }
    static public function get_by_maintainer(OpenpearMaintainer $maintainer, $limit = 20){
        try {
            $favorites = C(OpenpearFavorite)->find_all(Q::eq('maintainer_id', $maintainer->id()));
            $charges = C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $maintainer->id()));
            $ids = array();
            foreach($favorites as $f) $ids[] = $f->package_id();
            foreach($charges as $c) $ids[] = $c->package_id();
            return C(OpenpearTimeline)->find_all(new Paginator($limit), Q::in('package_id', array_unique($ids)), Q::order('-id'));
        } catch (Exception $e){
            return array();
        }
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
    
    public function atom_id(){
        return $this->id();
    }
    public function atom_title(){
        return $this->subject();
    }
    public function atom_published(){
        return $this->created();
    }
    public function atom_updated(){
        return $this->created();
    }
    public function atom_issued(){
        return $this->created();
    }
    public function atom_content(){
        return $this->description();
    }
    public function atom_summary(){
        return $this->description();
    }
    public function atom_author(){
        if($this->maintainer() instanceof OpenpearMaintainer){
            return str($this->maintainer());
        }
        return 'Openpear';
    }
    public function atom_href(){
        if($this->package() instanceof OpenpearPackage){
            return url('package/'. $this->package->name());
        } else if ($this->maintainer() instanceof OpenpearMaintainer){
            return url('maintainer/'. $this->maintainer->name());
        }
    }
}