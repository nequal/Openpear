<?php
class Package extends OpenpearFlow
{
    /**
     * パッケージ一覧
     * 
     * ## テンプレートにセットする値
     * # 'object_list' => パッケージオブジェクトの配列
     * # 'paginator' => Paginator
     */
    public function models(){
        $paginator = new Paginator(10, $this->in_vars('page', 1));
        switch(strtolower($this->in_vars('sort', 'released'))){
            case 'updates':
                $this->vars('object_list', C(OpenpearPackage)->find_page($this->in_vars('q'), $paginator, '-updated'));
                $this->template('package/models_updates.html');
                break;
            
            case 'favored':
                $this->vars('object_list', C(OpenpearPackage)->find_page($this->in_vars('q'), $paginator, '-favored_count'));
                $this->template('package/models_favored.html');
                break;
                
            case 'released':
            default:
                $this->vars('object_list', C(OpenpearPackage)->find_page($this->in_vars('q'), $paginator, '-released_at'));
                $this->template('package/models_released.html');
        }
        $this->vars('paginator', $paginator->add(array('q' => $this->in_vars('q'))));
        return $this;
    }
    
    /**
     * パッケージ詳細
     * 
     * ## テンプレートにセットする値
     * # 'object' => パッケージオブジェクト
     * # 'maintainers' => メンテナオブジェクトの配列
     * # 'recent_releases' => 最新リリースオブジェクトの配列
     * # 'changes'
     * # 'timelines'
     * # 'favored_maintainers'
     */
    public function model($package_name){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('object', $package);
        $this->vars('package', $package);
        $this->vars('maintainers', $package->maintainers());
        $releases = $package->releases();// TODO: sort
        $this->vars('recent_releases', empty($releases)?$releases:array_reverse($releases));
        // TODO: changes
        $this->vars('timelines', C(OpenpearTimeline)->find_all(new Paginator(10), Q::eq('package_id', $package->id()), Q::order('-id')));
        $this->vars('favored_maintainers', $package->favored_maintainers());
        return $this;
    }
    
    /**
     * Fav 登録
     */
    public function add_favorite($package_name){
        $this->_login_required('package/'. $package_name);
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $fav = new OpenpearFavorite();
            $fav->maintainer_id($user->id());
            $fav->package_id($package->id());
            $fav->save();
            C($fav)->commit();
        } catch(Exception $e){}
        Http::redirect(url('package/'. $package_name));
    }
    /**
     * Fav 削除
     */
    public function remove_favorite($package_name){
        $this->_login_required('package/'. $package_name);
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $fav = C(OpenpearFavorite)->find_get(Q::eq('maintainer_id', $user->id()), Q::eq('package_id', $package->id()));
            $fav->find_delete(Q::eq('maintainer_id', $user->id()), Q::eq('package_id', $package->id()));
            $fav->recount_favorites();
            C(OpenpearFavorite)->commit();
        } catch(Exception $e){}
        Http::redirect(url('package/'. $package_name));
    }
    public function add_maintainer($package_name){
        if($this->is_post() && $this->is_vars('maintainer_name')){
            $this->_login_required('package'. $package_name);
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $this->in_vars('maintainer_name')));
                $package->permission($this->user());
                $charge = new OpenpearCharge();
                $charge->maintainer_id($maintainer->id());
                $charge->package_id($package->id());
                $charge->save();
                C($charge)->commit();
            } catch(Exception $e){}
        }
        Http::redirect(url('package/'. $package_name. '/manage'));
    }
    public function remove_maintainer($package_name){
        if($this->is_post() && $this->is_vars('maintainer_id')){
            $this->_login_required('package'. $package_name);
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->in_vars('maintainer_id')));
                $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $package->id()), Q::eq('maintainer_id', $maintainer->id()));
                $charge->delete();
                C($charge)->commit();
            } catch(Exception $e){}
        }
        Http::redirect(url('package/'. $package_name. '/manage'));
    }
    
    /**
     * カテゴリ登録
     */
    public function add_tag($package_name){
        if($this->is_post() && $this->is_vars('tag_name')){
            $this->_login_required('package/'. $package_name);
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                // $package->permission($this->user());
                $package->add_tag($this->in_vars('tag_name'), $this->in_vars('prime', false));
                C($package)->commit();
            } catch(Exception $e){}
        }
        Http::redirect(url('package/'. $package_name));
    }
    public function remove_tag($package_name){
        if($this->is_post() && $this->is_vars('tag_id')){
            $this->_login_required('package/'. $package_name);
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package->remove_tag($this->in_vars('tag_id'));
                C($package)->commit();
            } catch(Exception $e){}
        }
        Http::redirect(url('package/'. $package_name. '/manage'));
    }
    public function prime_tag($package_name){
        if($this->is_post() && $this->is_vars('tag_id')){
            $this->_login_required('package/'. $package_name);
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package_tag = C(OpenpearPackageTag)->find_get(Q::eq('tag_id', $this->in_vars('tag_id')), Q::eq('package_id', $package->id()));
                $package_tag->prime(true);
                $package_tag->save();
                C($package_tag)->commit();
            } catch(Exception $e){}
        }
        Http::redirect(url('package/'. $package_name. '/manage'));
    }
    
    /**
     * パッケージ作成
     */
    public function create(){
        $this->_login_required('packages/create');
        if(!$this->is_post()){
            $this->cp(R(OpenpearPackage));
        }
        $this->template('package/create.html');
        return $this;
    }
    public function create_do(){
        $this->_login_required('packages/create');
        $user = $this->user();
        if($this->is_post()){
            try {
                $package = new OpenpearPackage();
                $package->set_vars($this->vars());
                $package->author_id($user->id());
                $package->save();
                $package->add_maintainer($user);
                C($package)->commit();
                Http::redirect(url('package/'. $package->name()));
            } catch(Exception $e){}
        }
        return $this->create();
    }

    public function manage($package_name){
        $this->_login_required('packages/update');
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            return $this;
        } catch(Exception $e){}
        Http::redirect(url('package/'. $package_name));
    }

    /**
     * パッケージ情報更新
     */
    public function edit($package_name)
    {
        $this->_login_required('packages/update');
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            if(!$this->is_post()){
                foreach(array('id', 'name', 'description', 'url', 'public_level', 'external_repository', 'external_repository_type', 'license') as $k){
                    if ($k == 'external_repository') {
                        $p = $package->{$k}();
                        if (!empty($p)) {
                            $this->vars('repository_uri_select', '2');
                        }
                        $this->vars($k, $p);
                    }
                    else {
                        $this->vars($k, $package->{$k}());
                    }
                }
            }
            return $this;
        } catch(Exception $e){}
        Http::redirect(url('package/'. $package_name));
    }

    /*
    public function update_confirm(){
        $this->_login_required('packages/update');
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('package_id')));
            $package->permission($this->user());
            $package->set_vars($this->vars());
            $this->vars('packge', $package);
            return $this;
        } catch(Exception $e){}
        Http::redirect(url());
    }
    */
    public function edit_do($package_name){
        $this->_login_required('packages/update');
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('id')));
            $package->permission($this->user());
            $this->vars('name', $package->name());
            $package->set_vars($this->vars());
            $package->save();
            C($package)->commit();
            // $this->success_redirect();
            Http::redirect(url('package/'. $package->name(). '/manage'));
        } catch(Exception $e){}
        return $this->update($package_name);
    }

    /**
     * Downloads
     */
    public function downloads($package_name)
    {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('object', $package);
        $this->vars('package', $package);
        $this->vars('maintainers', $package->maintainers());
        $releases = $package->releases();// TODO: sort
        $this->vars('recent_releases', empty($releases)?$releases:array_reverse($releases));
        // TODO: changes
        return $this;
    }
}
