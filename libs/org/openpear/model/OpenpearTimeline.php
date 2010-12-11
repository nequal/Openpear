<?php
import('org.rhaco.storage.db.Dao');
import('org.rhaco.net.xml.Atom');

/**
 * Timeline
 *
 * @var serial $id
 * @var string $subject @{"require":true}
 * @var text $description @{"require":true}
 * @var choice $type @{"choices":["release","changeset","user_activities","package_setting","favorite"]}
 * @var integer $package_id
 * @var integer $maintainer_id
 * @var timestamp $created
 */
class OpenpearTimeline extends Dao implements AtomInterface
{
    protected $id;
    protected $subject;
    protected $description;
    protected $type;
    protected $package_id;
    protected $maintainer_id;
    protected $created;
    
    private $package;
    private $maintainer;
    
    protected function __init__(){
        $this->created = time();
    }
    protected function __create_verify__() {
        if ($this->type == 'favorite') {
            try {
                $timeline = C(__CLASS__)->find_get(
                    Q::eq('type', 'favorite'),
                    Q::eq('maintainer_id', $this->maintainer_id),
                    Q::eq('package_id', $this->package_id)
                );
                if ($timeline instanceof OpenpearTimeline) {
                    Exceptions::add(new RuntimeException());
                    return false;
                }
            } catch (NotfoundDaoException $e) {
                # pass
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
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
                $this->package = OpenpearPackage::get_package($this->package_id());
            }catch(Exception $e){}
        }
        return $this->package;
    }
    public function maintainer(){
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = OpenpearMaintainer::get_maintainer($this->maintainer_id);
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
