<?php
require_once 'HatenaSyntax.php';
import('org.rhaco.service.OpenIDAuth');
import('org.rhaco.net.xml.Atom');
import('jp.nequal.pear.PackageProjector');
import('jp.nequal.net.Subversion');

module('exception.OpenpearException');
module('module.OpenpearAccountModule');
module('module.OpenpearTemplf');
module('model.OpenpearMaintainer');
module('model.OpenpearOpenidMaintainer');
module('model.OpenpearPackage');
module('model.OpenpearRelease');
module('model.OpenpearTag');
module('model.OpenpearPackage');
module('model.OpenpearRelease');
module('model.OpenpearReleaseQueue');
module('model.OpenpearCharge');
module('model.OpenpearTimeline');
module('model.OpenpearFavorite');

class Openpear extends Flow
{
	/**
	 * @context OpenpearTemplf $ot フィルタ
	 */
    protected function __init__(){
    	$this->add_module(new OpenpearAccountModule());
    	$this->vars('ot',new OpenpearTemplf($this->user()));
    }
    /**
     * OpenID でログインする
     * @request string $openid_url openid認証サーバのURL
     */
    public function login_by_openid(){
        if($this->is_login()) $this->redirect_method('dashboard');
        if(OpenIDAuth::login($openid_user, $this->in_vars('openid_url'))){
            try {
                $openid_maintainer = C(OpenpearOpenidMaintainer)->find_get(Q::eq('url', $openid_user->identity()));
                $this->user($openid_maintainer->maintainer());
                if($this->login()){
                	$this->redirect_by_map("success_redirect");
                }
            } catch(Exception $e){
                $this->sessions('openid_identity', $openid_user->identity());
                $this->redirect_method('signup');
            }
        }
        return $this->do_login();
    }
    /**
     * トップページ
     * 
     * @context integer $package_count パッケージ総数
     * @context $primary_tags primary がセットされているタグリスト(上限付き)
     * @context $recent_releases 最新 OpenpearRelease モデルの配列
     */
    public function index(){
        $this->vars('package_count', C(OpenpearPackage)->find_count());
        $this->vars('primary_tags', OpenpearPackage::getActiveCategories(16));
        $this->vars('recent_releases', C(OpenpearRelease)->find_page(null, new Paginator(20, 1), '-id'));
    }
	/**
	 * 検索のマッピング
	 * メンテナ検索かパッケージ検索へリダイレクト
	 * @request string $search_for 検索対象
	 * @request string $q 検索クエリ
	 */
    public function search(){
    	// TODO いる？
        switch($this->in_vars('search_for', 'packages')){
            case 'maintainers': $this->redirect_method('maintainer_search',array('q'=>$this->in_vars('q')));
            case 'packages':
            default: $this->redirect_method('packages',array('q'=>$this->in_vars('q')));
        }
        $this->redirect_method('index');
    }
    /**
     * パッケージ一覧
     * 
     * @context $object_list パッケージオブジェクトの配列
     * @context $paginator Paginator
     */
    public function packages(){
    	// TODO どこに分岐してる?
    	// TODO テンプレをここで指定しない方がいい
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
    }    
    /**
     * パッケージ詳細
     * 
     * @param string $package_name パッケージ名
     * @context OpenpearPackage $object パッケージオブジェクト
     * @context OpenpearPackage $package パッケージオブジェクト
     * @context OpenpearMaintainer[] $maintainers メンテナオブジェクトの配列
     * @context recent_releases  最新リリースオブジェクトの配列
     */
    public function package($package_name){
    	// TODO 仕様の確認
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('object', $package);
        $this->vars('package', $package);
        $this->vars('maintainers', $package->maintainers());
        $releases = $package->releases();// TODO : sort
        $this->vars('recent_releases', empty($releases) ? $releases : array_reverse($releases));
        // TODO changes
        $this->vars('timelines', C(OpenpearTimeline)->find_all(new Paginator(10), Q::eq('package_id', $package->id()), Q::order('-id')));
        $this->vars('favored_maintainers', $package->favored_maintainers());
    }
    /**
     * ダッシュボード
     * @context OpenpearMaintainer $maintainer ログインしてるメンテナ
     * @context OpenpearCharge[] $my_package_charges
     * @context OpenpearTimeline[] $timelines
     * @context OpenpearFavorite[] $my_favorites
     * @context OpenpearMessage[] $notices
     */
    public function dashboard(){
        $this->login_required();
        $this->vars('maintainer', $this->user());
        $this->vars('my_package_charges', C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $this->user()->id())));
        $this->vars('timelines', OpenpearTimeline::get_by_maintainer($this->user()));
        $this->vars('my_favorites', C(OpenpearFavorite)->find_all(Q::eq('maintainer_id', $this->user()->id())));
        $this->vars('notices', C(OpenpearMessage)->find_all(Q::eq('maintainer_to_id', $this->user()->id()), Q::eq('type', 'system_notice'), Q::eq('unread', true)));
    }
    /**
     * メッセージを閉じる？ ajaxで使う？
     * @request integer $message_id メッセージID
     */
    public function dashboard_message_hide(){
        $this->login_required();
        // TODO 仕様の確認
        try {
            if($this->is_post() && $this->is_vars('message_id')){
                $message = C(OpenpearMessage)->find_get(Q::eq('id', $this->in_vars('message_id')), Q::eq('maintainer_to_id', $this->user()->id()));
                $message->unread(false);
                $message->save(true);
                echo 'ok';
            }
        } catch(Exception $e){}
        exit;
    }
    /**
     * 新規登録フォーム
     * @context boolean $openid
     */
    public function signup(){
    	// TODO 仕様の確認
        if($this->in_sessions('openid_identity')){
            $this->vars('openid', true);
            $this->vars('openid_identity', $this->in_sessions('openid_identity'));
        } else $this->vars('openid', false);
        if(!$this->is_post()){
            $this->cp(R(OpenpearMaintainer));
        }
    }
    /**
     * 新規登録を実行する
     */
    public function signup_do(){
    	// TODO 仕様の確認
        if($this->is_post()){
            $account = new OpenpearMaintainer();
            try {
                $account->cp($this->vars());
                $account->new_password($this->in_vars('new_password'));
                $account->new_password_conf($this->in_vars('new_password_conf'));
                $account->save();

                if($this->is_sessions('openid_identity')){
                    $openid_maintainer = new OpenpearOpenidMaintainer();
                    $openid_maintainer->maintainer_id($account->id());
                    $openid_maintainer->url($this->in_sessions('openid_identity'));
                    $openid_maintainer->save();
                    $this->rm_sessions('openid_identity');
                }
                C($account)->commit();
            } catch(Exception $e){
            	$this->save_exception($e);
				$this->redirect_method('signup');
            }
            $this->user($account);
            parent::login();
            $this->redirect_by_map("success_redirect");
        }
        $this->redirect_by_map("fail_redirect");
    }
    /**
     * パッケージの詳細
     * @param string $package_name パッケージ名
     * @param string $path リポジトリのパス
     * @request string $lang ロケール
     * @request string $tag タグ
     * @context OpenpearPackage $package パッケージ
     * @context string $body 説明
     */
    public function browse($package_name, $path='README'){
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        // TODO pathはなんだろう
        $path = ltrim($path, ' /.');
        $lang = $this->in_vars('lang', App::lang());
        $root = $this->is_vars('tag')? sprintf('tags/doc/%s', $this->in_vars('tag')): 'doc';
        $root = File::absolute(module_const('svn_root'), implode('/', array($package->name(), $root, $lang)));
        $repo_path = File::absolute($root, $path);
        $this->vars('package', $package);
        $body = Subversion::cmd('cat', array($repo_path));
        if(empty($body)){
            Http::status_header(404);
            $body = '* Not found.'. PHP_EOL;
            $body .= 'Requested page is not found in our repositories.';
        }
        $this->vars('body', HatenaSyntax::render($body));
        // TODO tree は何が入る？
        $this->vars('tree', Subversion::cmd('list', array($root), array('recursive' => 'recursive')));
    }
    /**
     * メンテナのプロフィール
     * @param string $maintainer_name メンテナのアカウント名
     * @context OpenpearMaintainer $object メンテナ
     * @context OpenpearCharge[] $charges
     * @context OpenpearFavorite[] $favorites
     * @context OpenpearTimeline[] $timelines
     */
    public function maintainer_profile($maintainer_name){
    	// TODO 仕様の確認
        try {
            $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        } catch(Exception $e){
            return $this->not_found();
        }
        $this->vars('object', $maintainer);
        $this->vars('charges', C(OpenpearCharge)->find_all(Q::eq('maintainer_id', $maintainer->id())));
        $this->vars('favorites', C(OpenpearFavorite)->find_all(Q::eq('maintainer_id', $maintainer->id())));
        $this->vars('timelines', C(OpenpearTimeline)->find_all(new Paginator(10), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id')));
    }
    /**
     * メンテナ検索
     * @request integer $page ページ番号
     * @request string $q 検索クエリ
     * @context OpenpearMaintainer[] $object_list メンテナ一覧
     * @context Paginator $paginator ページネータ
     */
    public function maintainer_search(){
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMaintainer)->find_page($this->in_vars('q'), $paginator), 'name');
        $this->vars('paginator', $paginator->add(array('q' => $this->in_vars('q'))));
    }
    /**
     * メンテナ情報を更新して結果をjsonで出力
     */
    public function maintainer_update_json(){
    	// TODO 仕様の確認
        if(!$this->is_login()){
            return Text::ououtput_jsonp(array('status' => 'ng', 'error' => 'required sign-in'));
        }
        try {
            if(!$this->is_post()) throw new OpenpearException('request method is unsupported');
            $maintainer = $this->user();
            $maintainer->cp($this->vars());
            $maintainer->save(true);
            Exceptions::validation();
        } catch(Exception $e){
            return Text::ououtput_jsonp(array('status' => 'ng', 'error' => $e->getMessage()));
        }
        return Text::ououtput_jsonp(array('status' => 'ok'));
    }
    /**
     * メッセージ詳細
     * @param integer $id メッセージID
     * @context OpenpearMessage $object メッセージ
     */
    public function message($id){
        $this->login_required();
        $user = $this->user();
        try {
            $message = C(OpenpearMessage)->find_get(Q::eq('id', $id));
            if($massage->permission($user)){
                if($message->maintainer_to_id() === $user->id()){
                    $message->unread(false);
                    $message->save(true);
                }
                $this->vars('object', $message);
                return;
            }
        } catch(Exception $e){}
        $this->redirect_by_map("fail_redirect");
    }
    /**
     * 受信箱
     * @request integer $page ページ番号
     * @context OpenpearMessage[] $object_list メッセージ一覧
     * @context Paginator $paginator ページネータ
     */
    public function inbox(){
        $this->login_required();
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
            $paginator, Q::eq('maintainer_to_id', $this->user()->id()), Q::order('-id')
        ));
        $this->vars('paginator', $paginator);
    }
    /**
     * 送信したメッセージ
     */
    public function sentbox(){
    	// TODO 仕様の確認
        $this->login_required();
        $user = $this->user();
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
           $paginator, Q::eq('maintainer_from_id', $user->id()), Q::order('-id')
        ));
    }
    /**
     * メッセージを送信？
     */
    public function compose(){
        $this->login_required();
    	// TODO 実装
    }
    /**
     * 送信確認？
     */
    public function send_confirm(){
    	// TODO 仕様の確認
        $this->login_required();
        if($this->is_post()){
            try {
                $message = new OpenpearMessage();
                $message->cp($this->vars());
                $message->save();
                return;
            } catch(Exception $e){}
        }
        return $this->compose();
    }
    /**
     * 送信
     */
    public function send_do(){
    	// TODO 仕様の確認
        $this->login_required();
        if($this->is_post()){
            $message = new OpenpearMessage();
            $message->cp($this->vars());
            $message->save(true);
            $this->redirect_by_map("success_redirect");
        }
        $this->redirect_by_map("fail_redirect");
    }

    
    /**
     * Fav 登録
     * @param string $package_name パッケージ名
     */
    public function package_add_favorite($package_name){
        $this->login_required();
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $fav = new OpenpearFavorite();
            $fav->maintainer_id($user->id());
            $fav->package_id($package->id());
            $fav->save();
            C($fav)->commit();
        } catch(Exception $e){}
        $this->redirect_method('package',$package_name);
    }
    /**
     * Fav 削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_favorite($package_name){
    	// TODO 仕様の確認
        $this->login_required();
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
    /**
     * パッケージにメンテナを追加する
     * @param string $package_name 追加するパッケージ名
     * @request string $maintainer_name メンテナ名
     */
    public function package_add_maintainer($package_name){
        if($this->is_post() && $this->is_vars('maintainer_name')){
            $this->login_required('package'. $package_name);
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
        $this->redirect_method('package_manage',$package_name);
    }
    /**
     * パッケージからメンテナを削除する
     * @param string $package_name パッケージ名
     * @request integer $maintainer_id メンテナID
     */
    public function package_remove_maintainer($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        if($this->is_post() && $this->is_vars('maintainer_id')){
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->in_vars('maintainer_id')));
                $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $package->id()), Q::eq('maintainer_id', $maintainer->id()));
                $charge->delete();
                C($charge)->commit();
            } catch(Exception $e){}
        }
        $this->redirect_method('package_manage',$package_name);
    }
    
    /**
     * カテゴリ登録
     * @param string $package_name パッケージ名
     * @request string $tag_name タグ名
     */
    public function package_add_tag($package_name){
    	// TODO 仕様の確認
        if($this->is_post() && $this->is_vars('tag_name')){
            $this->login_required('package/'. $package_name);
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                // $package->permission($this->user());
                $package->add_tag($this->in_vars('tag_name'), $this->in_vars('prime', false));
                C($package)->commit();
            } catch(Exception $e){}
        }
        $this->redirect_method('package',$package_name);
    }
    /**
     * パッケージからタグの削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_tag($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        if($this->is_post() && $this->is_vars('tag_id')){
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package->remove_tag($this->in_vars('tag_id'));
                C($package)->commit();
            } catch(Exception $e){}
        }
        $this->redirect_method('package_manage',$package_name);
    }
    /**
     * ？？？
     * @param string $package_name パッケージ名
     */
    public function package_prime_tag($package_name){
    	// TODO 仕様の確認
        if($this->is_post() && $this->is_vars('tag_id')){
            $this->login_required('package/'. $package_name);
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
        $this->redirect_method('package_manage',$package_name);
    }
    
    /**
     * パッケージ作成
     */
    public function package_create(){
    	// TODO 仕様の確認
        $this->login_required();
        if(!$this->is_post()){
            $this->cp(new OpenpearPackage());
        }
    }
    /**
     * パッケージの作成
     */
    public function package_create_do(){
    	// TODO 仕様の確認
        $this->login_required();
        $user = $this->user();
        if($this->is_post()){
            try {
                $package = new OpenpearPackage();
                $package->cp($this->vars());
                $package->author_id($user->id());
                $package->save();
                $package->add_maintainer($user);
                C($package)->commit();
		        $this->redirect_method('package',$package->name());
            } catch(Exception $e){}
        }
        $this->redirect_method("package_create");
    }
	/**
	 * パッケージ管理
	 * @param string $package_name パッケージ名
	 */
    public function package_manage($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            return;
        } catch(Exception $e){}
		$this->redirect_method('package',$package_name);
    }

    /**
     * パッケージ情報更新
     * @param string $package_name パッケージ名
     */
    public function package_edit($package_name)
    {
		// TODO 仕様の確認
        $this->login_required();
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
            return;
        } catch(Exception $e){}
        $this->redirect_method('package',$package_name);
    }

    /*
    public function update_confirm(){
        $this->login_required('packages/update');
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('package_id')));
            $package->permission($this->user());
            $package->cp($this->vars());
            $this->vars('packge', $package);
            return $this;
        } catch(Exception $e){}
        Http::redirect(url());
    }
    */
    /**
     * パッケージ情報更新
     * @param string $package_name パッケージ名
     */
    public function package_edit_do($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('id')));
            $package->permission($this->user());
            $this->vars('name', $package->name());
            $package->cp($this->vars());
            $package->save();
            C($package)->commit();
            $this->redirect_method('package_manage',$package->name());
        } catch(Exception $e){}
        return $this->update($package_name);
    }

    /**
     * Downloads
     * @param string $package_name パッケージ名
     */
    public function package_downloads($package_name)
    {
    	// TODO 仕様の確認
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('object', $package);
        $this->vars('package', $package);
        $this->vars('maintainers', $package->maintainers());
        $releases = $package->releases();// TODO sort
        $this->vars('recent_releases', empty($releases)?$releases:array_reverse($releases));
        // TODO  changes
    }
	/**
	 * パッケージのリリース
	 * @param string $package_name パッケージ名
	 * @context integer $package_id パッケージID
	 * @context OpenpearPackage $package パッケージ
	 */
    public function package_release($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if(!$this->is_post()){
            $this->vars('package_id', $package->id());
            $this->cp(new PackageProjectorConfig());
        }
        $this->vars('package', $package);
        $this->vars('package_id', $package->id());
    }
    /**
     * パッケージのリリースの確認？
     * @param string $package_name パッケージ名
     */
    public function package_release_confirm($package_name){
        $this->login_required();
        // TODO テンプレを外で定義する
        // TODO 仕様の確認
        $this->template('package/release_confirm.html');
        if($this->is_post()){
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('package_id')));
                $charge = $package->permission($this->user());
                $build_conf = new PackageProjectorConfig();
                $build_conf->cp($this->vars());
                if($this->is_vars('extra_conf')) $build_conf->parse_ini_string($this->in_vars('extra_conf'));
                foreach(C(OpenpearCharge)->find(Q::eq('package_id', $package->id())) as $charge){
                    $build_conf->maintainer(R(PackageProjectorConfigMaintainer)->set_charge($charge));
                }
                $build_conf->package_package_name($package->name());
                $this->sessions('openpear_release_vars', $this->vars());
                $this->vars('package', $package);
                return $this;
            } catch(Exception $e){
                return $this->package_release($package_name);
            }
        }
		$this->redirect_method('package',$package_name);
    }
    /**
     * パッケージのリリース
     * @param string $package_name パッケージ名
     */
    public function package_release_do($package_name){
    	// TODO 仕様の確認
        $this->login_required();
        if($this->is_post() && $this->is_sessions('openpear_release_vars')){
            $this->cp($this->in_sessions('openpear_release_vars'));
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('package_id')));
                $package->permission($this->user());
                $build_conf = new PackageProjectorConfig();
                $build_conf->cp($this->vars());
                if($this->is_vars('extra_conf')) $build_conf->parse_ini_string($this->in_vars('extra_conf'));
                foreach(C(OpenpearCharge)->find(Q::eq('package_id', $package->id())) as $charge){
                    $build_conf->maintainer(R(PackageProjectorConfigMaintainer)->set_charge($charge));
                }
                $build_conf->package_package_name($package->name());
                $release_queue = new OpenpearReleaseQueue();
                $release_queue->cp($this->vars());
                $release_queue->package_id($package->id());
                $release_queue->maintainer_id($this->user()->id());
                $release_queue->build_conf($build_conf->get_ini());
                $release_queue->save();
                C($release_queue)->commit();
                Http::redirect(url('package/'. $package->name(). '/manage/release_queue_added'));
            } catch(Exception $e){
                return $this->package_release($package_name);
            }
        }
		$this->redirect_method('package',$package_name);
    }
    
    /**
     * Downloads
     * 
     * @context OpenpearPackage $package パッケージオブジェクト
     * @context OpenpearRelease[] $object_list 最新リリースオブジェクトの配列
     */
    public function download($package_name){
    	// TODO 仕様の確認
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('package', $package);
        $this->vars('object_list', C(OpenpearRelease)->find_all(Q::eq('package_id', $package->id())));
    }
    
    
    // TODO Source
    protected $allowed_ext = array('php', 'phps', 'html', 'css', 'pl', 'txt', 'js', 'htaccess');
    static protected $__allowed_ext__ = 'type=string[]';
    
    /**
     * ？？？？？
     * @const string $svn_url リポジトリのURL
     */
    public function source_browse($package_name, $path=''){
    	// TODO 仕様の確認
    	// TODO SVNとの連携
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $path = rtrim(ltrim($path, ' /.'), '/');
        $root = $this->is_vars('tag')? sprintf('tags/%s', $this->in_vars('tag')): 'trunk';
        $local_root = File::absolute(module_const('svn_root'), implode('/', array($package->name(), $root)));
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
        	$this->redirect_method('package',$package_name);
        }
        $this->vars('path', $path);
        $this->vars('info', self::format_info($info));
        $this->vars('package', $package);
        $this->vars('real_url', File::absolute(module_const('svn_url'), implode('/', array($package->name(), $root, $path))));
        $this->vars('externals', Subversion::cmd('propget', array('svn:externals', $info['url'])));
    }
    /**
     * ？？？？
     * @param string $package_name
     * @param string $tag
     * @param string $path
     */
    public function browse_tag($package_name, $tag, $path){
    	// TODO なにするもの？
        $this->vars('tag', $tag);
        return $this->browse($package_name, $path);
    }
    /**
     * SVNチェンジセットの詳細
     * @param string $package_name パッケージ名
     * @param integer $revision リビジョン番号
     * @context OpenpearPackage $package パッケージ
     * @context OpenpearChangeset $changeset チェンジセット
     * @const string $svn_root　リポジトリのルートパス
     */
    public function changeset($package_name, $revision){
    	// TODO SVNとの連携
        $revision = intval($revision);
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $changeset = C(OpenpearChangeset)->find_get(Q::eq('revision', $revision), Q::eq('package_id', $package->id()));
        $path = File::absolute(module_const('svn_root'), $package->name());
        $log = Subversion::cmd('log', array($path), array('revision' => $revision, 'limit' => 1));
        $diff = Subversion::cmd('diff', array($path), array('revision' => sprintf('%d:%d', $revision-1, $revision)));
        $this->vars('package', $package);
        $this->vars('changeset', $changeset);
        $this->vars('log', $log);
        $this->vars('diff', $diff);
    }
    
    /**
     * ？？？？
     * @param array $tree
     * @return array
     */
    static public function format_tree(array $tree){
    	// TODO 仕様の確認
        foreach($tree as &$f){
            try {
                $f['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $f['commit']['author']));
                $log = Subversion::cmd('log', array(module_const('svn_root')), array('revision' => $f['commit']['revision'], 'limit' => 1));
                $f['log'] = array_shift($log);
            } catch(Exception $e){}
        }
        // Log::d($tree);
        return $tree;
    }
    /**
     * SVNからログを取得
     * @param array $info
     * @return array
     */
    static public function format_info(array $info){
    	// TODO 仕様の確認
    	// TODO Subversion::cmdの実装
        $log = Subversion::cmd('log', array($info['url']), array('limit' => 1));
        $info['recent'] = array_shift($log);
        $info['recent']['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $info['recent']['author']));
        return $info;
    }
    /**
     * パッケージのタイムライン？
     * @param string $package_name パッケージ名
     */
    public function package_timeline($package_name){
    	// TODO　仕様の確認
        Http::redirect(url('package/'. $package_name));
    }
    
    /**
     * タイムラインをAtomフィードで出力
     */
    public function timeline_atom(){
    	// TODO 仕様の確認
        Atom::convert('Openpear Timelines', url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::order('-id'))
        )->output();
    }
    /**
     * ？？？？
     * @param string $package_name パッケージ名
     */
    public function timeline_atom_package($package_name){
    	// TODO 仕様の確認
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        Atom::convert('Openpear Package Timelines: '. $package->name(), url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::eq('package_id', $package->id()), Q::order('-id'))
        )->output();
    }
    /**
     * メンテナのタイムラインをAtomフィードで出力
     * @param string $maintainer_name メンテナのアカウント名
     */
    public function timeline_atom_maintainer($maintainer_name){
    	// TODO 仕様の確認
        $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        Atom::convert('Openpear Maintainer Timelines: '. $maintainer->name(), url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id'))
        )->output();
    }
}
