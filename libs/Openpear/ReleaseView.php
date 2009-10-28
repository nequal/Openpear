<?php
import('org.rhaco.net.xml.Atom');
import('jp.nequal.pear.PackageProjector');

class ReleaseView extends Openpear
{
    public function package_release($package_name){
        $this->_login_required('package/'. $package_name);
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if(!$this->isPost()){
            $this->vars('package_id', $package->id());
            $this->cp(new PackageProjectorConfig());
        }
        $this->vars('package', $package);
        $this->vars('package_id', $package->id());
        $this->template('package/release.html');
        return $this;
    }
    public function package_release_confirm($package_name){
        $this->_login_required('package/'. $package_name);
        $this->template('package/release_confirm.html');
        if($this->isPost()){
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->inVars('package_id')));
                $charge = $package->permission($this->user());
                $build_conf = new PackageProjectorConfig();
                $build_conf->set_vars($this->vars());
                if($this->isVars('extra_conf')) $build_conf->parse_ini_string($this->inVars('extra_conf'));
                foreach(C(OpenpearCharge)->find(Q::eq('package_id', $package->id())) as $charge){
                    $build_conf->maintainer(R(PackageProjectorConfigMaintainer)->set_charge($charge));
                }
                $build_conf->package_package_name($package->name());
                $this->sessions('openpear_release_vars', $this->vars());
                $this->vars('package', $package);
                return $this;
            } catch(Exception $e){
                Exceptions::add($e);
                return $this->package_release($package_name);
            }
        }
        Header::redirect(url('package/'. $package_name));
    }
    public function package_release_do($package_name){
        $this->_login_required('package/'. $package_name);
        if($this->isPost() && $this->isSessions('openpear_release_vars')){
            $this->cp($this->inSessions('openpear_release_vars'));
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->inVars('package_id')));
                $package->permission($this->user());
                $build_conf = new PackageProjectorConfig();
                $build_conf->set_vars($this->vars());
                if($this->isVars('extra_conf')) $build_conf->parse_ini_string($this->inVars('extra_conf'));
                foreach(C(OpenpearCharge)->find(Q::eq('package_id', $package->id())) as $charge){
                    $build_conf->maintainer(R(PackageProjectorConfigMaintainer)->set_charge($charge));
                }
                $build_conf->package_package_name($package->name());
                $release_queue = new OpenpearReleaseQueue();
                $release_queue->set_vars($this->vars());
                $release_queue->package_id($package->id());
                $release_queue->maintainer_id($this->user()->id());
                $release_queue->build_conf($build_conf->get_ini());
                $release_queue->save();
                C($release_queue)->commit();
                Http::redirect(url('package/'. $package->name(). '/manage/release_queue_added'));
            } catch(Exception $e){
                Exceptions::add($e);
                return $this->package_release($package_name);
            }
        }
        Header::redirect(url('package/'. $package_name));
    }
    
    /**
     * Downloads
     * 
     * ## テンプレートにセットする値
     * # 'package' => パッケージオブジェクト
     * # 'object_list' => 最新リリースオブジェクトの配列
     */
    public function download($package_name){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('package', $package);
        $this->vars('object_list', C(OpenpearRelease)->find_all(Q::eq('package_id', $package->id())));
        return $this;
    }
}