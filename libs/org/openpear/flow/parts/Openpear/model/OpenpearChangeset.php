<?php
import('org.rhaco.storage.db.Dao');

class OpenpearChangeset extends Dao
{
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
    
    protected function __after_create__(){
        // 美しくない
        $path = preg_replace('@^file://@', '', module_const('svn_root'));
        $message = Subversion::look('log', array($path), array('revision' => $this->revision));
        $timeline = new OpenpearTimeline();
        $timeline->subject(sprintf('<a href="%s">%s</a> <span class="hl">committed</span> to <a href="%s">%s</a>',
            url('maintainer/'. $this->maintainer()->name()),
            $this->maintainer()->name(),
            url('package/'. $this->package()->name()),
            $this->package()->name()
        ));
        $timeline->description(sprintf('Changeset <a href="%s">[%d]</a>.<br />%s',
            url(sprintf('package/%s/changeset/%d', $this->package()->name(), $this->revision)),
            $this->revision,
            nl2br(htmlspecialchars(Text::substring($message, 0, 200, 'utf-8'), ENT_QUOTES))
        ));
        $timeline->type('changeset');
        $timeline->package_id($this->package_id());
        $timeline->maintainer_id($this->maintainer_id());
        $timeline->save();
    }
    
    static public function commit_hook($path, $revision, $message){
        Log::debug(sprintf('commit hook: %s %d "%s"', $path, $revision, $message));
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
            $changeset->changed(serialize($parsed_changed));
            $changeset->save(true);
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