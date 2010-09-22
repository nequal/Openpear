<?php
import('org.rhaco.storage.db.Dao');

class OpenpearNewprojectQueue extends Dao
{    
    protected $id;
    protected $package_id;
    protected $maintainer_id;
    protected $mail_possible = true;
    protected $settings;
    protected $trial_count = 0;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__ = 'type=integer,require=true';
    static protected $__maintainer_id__ = 'type=integer,require=true';
    static protected $__mail_possible__ = 'type=boolean';
    static protected $__settings__ = 'type=text';
    static protected $__trial_count__ = 'type=integer';
    static protected $__created__ = 'type=timestamp';
    
    private $package;
    private $maintainer;
    
    protected function __init__(){
        $this->trial_count = 0;
        $this->created = time();
    }
    public function create(){
        try {
            Subversion::cmd('import', array(
                OpenpearConfig::svn_skeleton(work_path('skeleton')),
                File::absolute(OpenpearConfig::svn_root(), $this->package()->name()),
            ),array(
                'message' => sprintf('[New Package] %s (@%s)',
                    $this->package()->name(),
                    $this->maintainer()->name()
                ),
                'username' => OpenpearConfig::system_user('openpear'),
            ));
            // $message = new OpenpearMessage();
            // $message->subject('New Package is ready for your commit!');
            $this->delete();
            C($this)->commit();
        } catch (Exception $e){
            Log::error($e->getMessage());
            $this->trial_count += 1;
            $this->save();
            C($this)->commit();
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
}
