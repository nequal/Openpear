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
    
    private $package;
    private $maintainer;
    
    /**
     * 初期化
     */
    protected function __init__(){
        $this->created = time();
    }
    /**
     * changed を unserialize してオブジェクトの配列を返す
     */
    protected function __fm_changed__(){
        $objects = array();
        $changed = unserialize($this->changed);
        foreach($changed as $c){
            $obj = new OpenpearChangesetChanged();
            $obj->cp($c);
            $objects[] = $obj;
        }
        return $objects;
    }
    /**
     * 作成後処理
     */
    protected function __after_create__(){
        // TODO 美しくない
        $path = preg_replace('@^file://@', '', OpenpearConfig::svn_root());
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
        $author = trim(Subversion::look('author', array($path), array('revision' => $revision)));
        $parsed_changed = self::parse_svnlook_changed($changed);
        list($package_name) = explode('/', $parsed_changed[0]['path']);
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $maintainer = null;
            try {
                if($author == OpenpearConfig::system_user('openpear') && preg_match('/\(@(.*?)\)$/', trim($message), $match)){
                    $author = $match[1];
                }
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $author));
            } catch(Exception $e){
                Log::error($e);
                throw $e;
            }
            
            $changeset = new self();
            $changeset->revision($revision);
            if($maintainer instanceof OpenpearMaintainer) $changeset->maintainer_id($maintainer->id());
            $changeset->package_id($package->id());
            $changeset->changed(serialize($parsed_changed));
            $changeset->save();
            
            $package->recent_changeset($changeset->revision());
            $package->save(true);
        } catch(Exception $e){
            throw $e;
        }
        try {
            chdir(OpenpearConfig::working_copy());
            ob_start();
            passthru('svn up');
            ob_end_clean();
        } catch(Exception $e){}
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
}
