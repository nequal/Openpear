<?php
import('jp.nequal.net.Subversion');

class SourceView extends Openpear
{
    protected $allowed_ext = array('php', 'phps', 'html', 'css', 'pl', 'txt', 'js', 'htaccess');
    static protected $__allowed_ext__ = 'type=string[]';
    
    public function browse($package_name, $path=''){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $path = rtrim(ltrim($path, ' /.'), '/');
        $root = $this->isVars('tag')? sprintf('tags/%s', $this->inVars('tag')): 'trunk';
        $local_root = File::absolute(def('svn_root'), implode('/', array($package->name(), $root)));
        $repo_path = File::absolute($local_root, $path);
        $info = Subversion::cmd('info', array($repo_path));
        if($info['kind'] === 'dir'){
            $this->template('package/source.html');
            $this->vars('tree', self::format_tree(Subversion::cmd('list', array($info['url']))));
        } else if($info['kind'] === 'file') {
            $this->template('package/source_viewfile.html');
            $p = explode('.', $info['path']);
            $ext = array_pop($p);
            if(in_array($ext, $this->allowed_ext)){
                $this->vars('code', Subversion::cmd('cat', array($info['url'])));
            }
        } else {
            Http::redirect(url('package/'. $package_name));
        }
        $this->vars('path', $path);
        $this->vars('info', self::format_info($info));
        $this->vars('package', $package);
        $this->vars('real_url', File::absolute(def('svn_url'), implode('/', array($package->name(), $root, $path))));
        $this->vars('externals', Subversion::cmd('propget', array('svn:externals', $info['url'])));
        return $this;
    }
    public function browse_tag($package_name, $tag, $path){
        $this->vars('tag', $tag);
        return $this->browse($package_name, $path);
    }
    public function changeset($package_name, $revision){
        $revision = intval($revision);
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $changeset = C(OpenpearChangeset)->find_get(Q::eq('revision', $revision), Q::eq('package_id', $package->id()));
        $path = File::absolute(def('svn_root'), $package->name());
        $log = Subversion::cmd('log', array($path), array('revision' => $revision, 'limit' => 1));
        $diff = Subversion::cmd('diff', array($path), array('revision' => sprintf('%d:%d', $revision-1, $revision)));
        $this->vars('package', $package);
        $this->vars('changeset', $changeset);
        $this->vars('log', $log);
        $this->vars('diff', $diff);
        return $this;
    }
    
    static public function format_tree(array $tree){
        foreach($tree as &$f){
            try {
                $f['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $f['commit']['author']));
                $log = Subversion::cmd('log', array(def('svn_root')), array('revision' => $f['commit']['revision'], 'limit' => 1));
                $f['log'] = array_shift($log);
            } catch(Exception $e){}
        }
        // Log::d($tree);
        return $tree;
    }
    static public function format_info(array $info){
        $log = Subversion::cmd('log', array($info['url']), array('limit' => 1));
        $info['recent'] = array_shift($log);
        $info['recent']['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $info['recent']['author']));
        return $info;
    }
}
