<?php
import('org.rhaco.storage.db.Dao');
import('org.openpear.pear.PackageProjector');
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
    
    protected function __init__(){
        $this->trial_count = 0;
        $this->created = time();
    }
}