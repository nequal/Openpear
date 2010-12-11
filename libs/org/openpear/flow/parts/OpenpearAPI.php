<?php

class OpenpearAPI extends Flow
{
    protected function __init__() {
        Log::disable_display();
        $this->add_module(new OpenpearAccountModule());
    }
    
    public function check_repo_exists() {
        $this->login_required();
        try {
            $package = OpenpearPackage::get_package($this->in_vars('package_id'));
            $info = Subversion::cmd('info', array(File::absolute(OpenpearConfig::svn_root(), implode('/', array($package->name(), 'trunk', $this->in_vars('dir', ''))))));
            if (isset($info['kind']) && $info['kind'] == 'dir') {
                echo 'ok';
            } else {
                throw new RuntimeException('directory is not found');
            }
        } catch (Exception $e) {
            Log::debug($e);
            echo 'ng';
        }
        exit;
    }
}
