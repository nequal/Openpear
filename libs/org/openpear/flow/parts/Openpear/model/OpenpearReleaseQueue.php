<?php
import('org.rhaco.storage.db.Dao');
import('jp.nequal.pear.PackageProjector');
import('jp.nequal.net.Subversion');

class OpenpearReleaseQueue extends Dao
{
    protected $id;
    protected $package_id;
    protected $maintainer_id;
    protected $revision;
    protected $build_path;
    protected $build_conf;
    protected $description;
    protected $notes;
    protected $trial_count;
    protected $created;
    
    static protected $__id__ = 'type=serial';
    static protected $__package_id__ = 'type=number,require=true';
    static protected $__maintainer_id__ = 'type=number,require=true';
    static protected $__revision__ = 'type=number,require=true';
    static protected $__build_path__ = 'type=string';
    static protected $__build_conf__ = 'type=text,require=true';
    static protected $__description__ = 'type=text';
    static protected $__notes__ = 'type=text';
    static protected $__trial_count__ = 'type=number';
    static protected $__created__ = 'type=timestamp';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function __init__(){
        $this->trial_count = 0;
        $this->created = time();
    }
    
    public function build(){
        try {
            // working path
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
            $work_path = work_path('build/'. $package->name(). time());
            $src_path = $work_path. '/src';
            $release_path = $work_path. '/release';
            $conf_path = $work_path. '/build.conf';
            
            File::rm($work_path);
            File::mkdir($work_path);
            File::mkdir($release_path);
            File::write($conf_path, $this->build_conf());
            
            // svn export
            $svn_vars = array($this->get_build_path(), $src_path);
            Subversion::cmd('export', $svn_vars);
            
            // build
            $project = PEAR_PackageProjector::singleton()->load($work_path);
            $project->configure($conf_path);
            $project->make();
            
            $this->complete();
        } catch(Exception $e){
            throw($e);
            Log::error($e->getMessage());
            $this->trial_count += 1;
            $this->save();
            C($this)->commit();
        }
    }
    private function complete(){
        // release model
        // svn tag
    }
    
    private function get_build_path(){
        $path = File::absolute(module_const('svn_root'), $this->package()->name());
        $path = File::absolute($path, 'trunk');
        if ($this->is_build_path()){
            $path = File::absolute($path, $this->build_path());
        }
        return $path;
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