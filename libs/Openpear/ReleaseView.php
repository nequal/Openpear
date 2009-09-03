<?php
import('org.rhaco.net.xml.Atom');

class ReleaseView extends Openpear
{
    public function package_release($package_name){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('package', $package);
        if(!$this->isPost()){
            $this->vars('package_id', $package->id());
            // リリース情報はこっちじゃない！
            // $this->cp($package->latest_release());
        }
        $this->template('package/release.html');
        return $this;
    }
    public function package_release_confirm($package_name){
        $this->template('package/release_confirm.html');
        if($this->isPost()){
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->inVars('package_id')));
                $release = new OpenpearRelease();
                $release->set_vars($this->vars());
                $release->save();
                return $this;
            } catch(Exception $e){
                Exceptions::add($e);
                return $this->package_release($package_name);
            }
        }
        Header::redirect(url('package/'. $package_name));
    }
    public function package_release_do($package_name){
        if($this->isPost()){
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->inVars('package_id')));
                $release = new OpenpearRelease();
                $release->set_vars($this->vars());
                $release->save();
                C($release)->commit();
                return $this;
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