<?php
require_once 'HatenaSyntax.php';
import('jp.nequal.net.Subversion');
import('org.openpear.Openpear.OpenpearFlow');

class Document extends OpenpearFlow
{
    public function browse($package_name, $path='README'){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $path = ltrim($path, ' /.');
        $lang = $this->inVars('lang', App::lang());
        $root = $this->isVars('tag')? sprintf('tags/doc/%s', $this->inVars('tag')): 'doc';
        $root = File::absolute(def('svn_root'), implode('/', array($package->name(), $root, $lang)));
        $repo_path = File::absolute($root, $path);
        $this->vars('package', $package);
        $body = Subversion::cmd('cat', array($repo_path));
        if(empty($body)){
            Http::status_header(404);
            $body = '* Not found.'. PHP_EOL;
            $body .= 'Requested page is not found in our repositories.';
        }
        $this->vars('body', HatenaSyntax::render($body));
        $this->vars('tree', Subversion::cmd('list', array($root), array('recursive' => 'recursive')));
        return $this;
    }
    public function browse_tag($package_name, $tag, $path){
        $this->vars('tag', $tag);
        return $this->browse($package_name, $path);
    }
}
