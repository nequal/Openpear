<?php
import('org.rhaco.storage.db.Dao');

class OpenpearChangeset extends Dao
{
    protected $_database_ = 'openpear';
    protected $_table_ = 'changeset';
    
    protected $revision;
    protected $maintainer_id;
    protected $package_id;
    protected $changed;
    protected $created;
    
    static protected $__revision__ = 'type=number,require=true,primary=true';
    static protected $__maintainer_id__ = 'type=number';
    static protected $__package_id__ = 'type=number,require=true';
    static protected $__changed__ = 'type=text';
    static protected $__created__ = 'type=timestamp';
    
    protected $package;
    protected $maintainer;
    static protected $__package__ = 'type=OpenpearPackage,extra=true';
    static protected $__maintainer__ = 'type=OpenpearMaintainer,extra=true';
    
    protected function __init__(){
        $this->created = time();
    }
    
    static public function commit_hook($path, $revision, $message){
        $changed = Subversion::look('changed', array($path), array('revision' => $revision));
        $author = Subversion::look('author', array($path), array('revision' => $revision));
        $parsed_changed = self::parse_svnlook_changed($changed);
        list($package_name) = explode('/', $parsed_changed[0]['path']);
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $maintainer = null;
            try {
                if($author === 'openpear' && preg_match('/\(@(.*?)\)$/', $message, $match)){
                    $author = $match[1];
                }
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', trim($author)));
            } catch(Exception $e){}
            
            $changeset = new self();
            $changeset->revision($revision);
            if($maintainer instanceof OpenpearMaintainer) $changeset->maintainer_id($maintainer->id());
            $changeset->package_id($package->id());
            $changeset->changed($parsed_changed);
            $changed->save();
            C($changed);
        } catch(Exception $e){
            throw $e;
        }
    }
    static public function parse_svnlook_changed($str){
        $result = array();
        $lines = explode("\n", $str);
        foreach($lines as $line){
            if(empty($line)) continue;
            $result[] = array(
                'status' => substr($line, 0, 2),
                'type' => (trim(substr($line, -1, 1)) == '/') ? 'dir' : 'file',
                'path' => trim(substr($line, 2)),
            );
        }
        return $result;
    }
    
    public function set_extra_objects(){
        if($this->package instanceof OpenpearPackage === false){
            try{
                $this->package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id()));
            }catch(Exception $e){}
        }
        if($this->maintainer instanceof OpenpearMaintainer === false){
            try{
                $this->maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id()));
            }catch(Exception $e){}
        }
    }
}