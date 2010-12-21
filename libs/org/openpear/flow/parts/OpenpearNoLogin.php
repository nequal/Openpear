<?php
/**
 * public actions
 *
 * @var string[] $allowed_ext
 * @var OpenpearMaintainer $user
 */
class OpenpearNoLogin extends Flow
{
    // TODO Source
    protected $allowed_ext = array('php', 'phps', 'html', 'css', 'pl', 'txt', 'js', 'htaccess');

    /**
     * @context OpenpearTemplf $ot フィルタ
     */
    protected function __init__() {
        $this->add_module(new OpenpearAccountModule());
        $this->vars('pear_domain', OpenpearConfig::pear_domain('openpear.org'));
        $this->vars('pear_alias', OpenpearConfig::pear_alias('openpear'));
        $this->vars('svn_url', OpenpearConfig::svn_url('http://svn.openpear.org'));
        $this->vars('ot', new OpenpearTemplf($this->user()));
        if ($this->is_login()) {
            $unread_messages_count = OpenpearMessage::unread_count($this->user());
            if ($unread_messages_count > 0) {
                $this->vars('unread_messages_count', $unread_messages_count);
            }
        }
    }
    /**
     * OpenID でログインする
     * @request string $openid_url openid認証サーバのURL
     */
    public function login_by_openid() {
        Pea::begin_loose_syntax();
        require_once 'Auth/OpenID/Consumer.php';
        require_once 'Auth/OpenID/FileStore.php';
        require_once 'Auth/OpenID/SReg.php';
        require_once 'Auth/OpenID/PAPE.php';
        $openid = null;

        if ($this->is_login()) $this->redirect_by_map('index');
        if ((($this->in_vars('openid_url') != "") || $this->in_vars('openid_verify'))) {
            Log::debug("begin openid auth: ". $this->in_vars('openid_url'));

            // OpenID Auth
            $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_FileStore(work_path('openid')));
            if ($this->is_vars('openid_verify')) {
                $response = $consumer->complete($this->request_url());
                if ($response->status == Auth_OpenID_SUCCESS) {
                    $openid = $response->getDisplayIdentifier();
                }
            } else {
                $auth_request = $consumer->begin($this->in_vars('openid_url'));
                if (!$auth_request) {
                    throw new RuntimeException('invalid openid url');
                }
                $sreg_request = Auth_OpenID_SRegRequest::build(array('nickname'), array('fullname', 'email'));
                if ($sreg_request) {
                    $auth_request->addExtension($sreg_request);
                }
                if ($auth_request->shouldSendRedirect()) {
                    $redirect_url = $auth_request->redirectURL(url(), $this->request_url(false). '?openid_verify=true');
                    if (Auth_OpenID::isFailure($redirect_url)) {
                        throw new RuntimeException("Could not redirect to server: {$redirect_url->message}");
                    } else {
                        $this->redirect($redirect_url);
                    }
                } else {
                    $form_html = $auth_request->htmlMarkup(url(),
                        $this->request_url(false). '?openid_verify=true', false, array('id' => 'openid_message'));
                    if (Auth_OpenID::isFailure($form_html)) {
                        throw new RuntimeException("Could not redirect to server: {$form_html->message}");
                    } else {
                        echo $form_html;
                        exit;
                    }
                }
            }

            Pea::end_loose_syntax();

            if ($openid) {
                try {
                    $openid_maintainer = C(OpenpearOpenidMaintainer)->find_get(Q::eq('url', $openid));
                    $this->user($openid_maintainer->maintainer());
                    if ($this->login()) {
                        $redirect_to = $this->in_sessions('logined_redirect_to');
                        $this->rm_sessions('logined_redirect_to');
                        if(!empty($redirect_to)) $this->redirect($redirect_to);
                        $this->redirect_by_map("login_redirect");
                    }
                } catch (NotfoundDaoException $e) {
                    $this->sessions('openid_identity', $openid);
                    $this->redirect_by_map('signup');
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }
        $this->do_login();
    }
    /**
     * トップページ
     *
     * @context integer $package_count パッケージ総数
     * @context $primary_tags primary がセットされているタグリスト(上限付き)
     * @context $recent_releases 最新 OpenpearRelease モデルの配列
     */
    public function index() {
        if (Store::has('index/package_count', 3600)) {
            $package_count = Store::get('index/package_count');
        } else {
            $package_count = C(OpenpearPackage)->find_count();
            Store::set('index/package_count', $package_count, 3600);
        }
        if (Store::has('index/primary_tags', 3600)) {
            $primary_tags = Store::get('index/primary_tags');
        } else {
            $primary_tags = OpenpearPackage::getActiveCategories(8);
            Store::set('index/primary_tags', $primary_tags, 3600);
        }
        if (Store::has('index/recent_releases', 3600)) {
            $recent_releases = Store::get('index/recent_releases');
        } else {
            $recent_releases = C(OpenpearPackage)->find_all(
                new Paginator(5),
                Q::neq('latest_release_id', null),
                Q::order('-released_at')
            );
            Store::set('index/recent_releases', $recent_releases, 3600);
        }
        if (Store::has('index/most_downloaded', 3600)) {
            $most_downloaded = Store::get('index/most_downloaded');
        } else {
            $most_downloaded = C(OpenpearPackage)->find_all(new Paginator(5), Q::order('-download_count'));
            Store::set('index/most_downloaded', $most_downloaded, 3600);
        }
        $this->vars('package_count', $package_count);
        $this->vars('primary_tags', $primary_tags);
        $this->vars('recent_releases', $recent_releases);
        $this->vars('most_downloaded', $most_downloaded);
    }
    /**
     * 検索のマッピング
     * メンテナ検索かパッケージ検索へリダイレクト
     * @request string $search_for 検索対象
     * @request string $q 検索クエリ
     */
    public function search() {
        // TODO いる？
        switch ($this->in_vars('search_for', 'packages')) {
            case 'maintainers': $this->redirect_method('maintainers',array('q'=>$this->in_vars('q')));
            case 'packages':
            default: $this->redirect_method('packages', array('q' => $this->in_vars('q')));
        }
        $this->redirect_by_map('index');
    }
    /**
     * タグ一覧
     * @context $primary_tags
     * @context $tags
     */
    public function packages_tags() {
        $this->vars('primary_tags', C(OpenpearTag)->find_all(Q::eq('prime', true)));
        $this->vars('tags', C(OpenpearTag)->find_all(Q::eq('prime', false)));
    }
    /**
     * パッケージ一覧
     *
     * @context $object_list パッケージオブジェクトの配列
     * @context $paginator Paginator
     */
    public function packages() {
        $sort = $this->in_vars('sort', '-released_at');
        $paginator = new Paginator(10, $this->in_vars('page', 1));
        if ($this->is_vars('category') && $this->in_vars('category') != '') {
            $tag = C(OpenpearTag)->find_get(Q::eq('name', $this->in_vars('category')));
            $this->vars('object_list', C(OpenpearPackage)->find_all(
                $paginator,
                Q::in('id', C(OpenpearPackageTag)->find_sub('package_id', Q::eq('tag_id', $tag->id()))),
                Q::order($sort)
            ));
            $paginator->vars('category', $this->in_vars('category'));
        } else {
            $this->vars('object_list', C(OpenpearPackage)->find_page($this->in_vars('q'), $paginator, $sort));
        }
        $paginator->vars('q', $this->in_vars('q'));
        $paginator->vars('sort', $sort);
        $this->vars('paginator', $paginator);
        $this->put_block($this->map_arg($sort{0} == '-'? substr($sort, 1): $sort, 'models_released.html'));
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
    public function package($package_name) {
        // TODO 仕様の確認
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        } catch (NotfoundDaoException $e) {
            $this->not_found($e);
        }
        $this->vars('object', $package);
        $this->vars('package', $package);
        $this->vars('maintainers', $package->maintainers());
        $this->vars('recent_releases', C(OpenpearRelease)->find_all(new Paginator(3), Q::eq('package_id', $package->id()), Q::order('-id')));
        // TODO changes
        $this->vars('timelines', C(OpenpearTimeline)->find_all(
            new Paginator(10),
            Q::eq('package_id', $package->id()),
            Q::order('-id')
        ));
        $this->vars('favored_maintainers', $package->favored_maintainers());
    }
    /**
     * 新規登録
     * @context boolean $openid
     */
    public function signup() {
        // TODO 仕様の確認
        if ($this->in_sessions('openid_identity')) {
            $this->vars('openid', true);
            $this->vars('openid_identity', $this->in_sessions('openid_identity'));
        } else {
            $this->vars('openid', false);
        }
        $account = new OpenpearMaintainer();
        try {
            if ($this->is_post()) {
                $account->cp($this->vars());
                $account->new_password($this->in_vars('new_password'));
                $account->new_password_conf($this->in_vars('new_password_conf'));
                $account->save();

                if ($this->is_sessions('openid_identity')) {
                    $openid_maintainer = new OpenpearOpenidMaintainer();
                    $openid_maintainer->maintainer_id($account->id());
                    $openid_maintainer->url($this->in_sessions('openid_identity'));
                    $openid_maintainer->save();
                    $this->rm_sessions('openid_identity');
                }
                $this->user($account);
                parent::login();
                $this->redirect_by_map("success_redirect");
            }
        } catch (Exception $e) {
            Dao::rollback_all();
            Log::debug($e);
        }
        $this->cp($account);
    }
    /**
     * メンテナのプロフィール
     * @param string $maintainer_name メンテナのアカウント名
     * @context OpenpearMaintainer $object メンテナ
     * @context OpenpearCharge[] $charges
     * @context OpenpearFavorite[] $favorites
     * @context OpenpearTimeline[] $timelines
     */
    public function maintainer_profile($maintainer_name) {
        try {
            $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        } catch (NotfoundDaoException $e) {
            $this->not_found($e);
        } catch (Exception $e) {
            // 共通エラーに飛ばす
            throw $e;
        }
        $this->vars('object', $maintainer);
        $this->vars('packages', C(OpenpearPackage)->find_all(Q::in('id', C(OpenpearCharge)->find_sub('package_id', Q::eq('maintainer_id', $maintainer->id()))), Q::order('-updated')));
        $this->vars('fav_packages', C(OpenpearPackage)->find_all(Q::in('id', C(OpenpearFavorite)->find_sub('package_id', Q::eq('maintainer_id', $maintainer->id()))), Q::order('-updated')));
        $this->vars('timelines', C(OpenpearTimeline)->find_all(new Paginator(10), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id')));
    }
    /**
     * メンテナ一覧
     * @request integer $page ページ番号
     * @request string $q 検索クエリ
     * @context OpenpearMaintainer[] $object_list メンテナ一覧
     * @context Paginator $paginator ページネータ
     */
    public function maintainers() {
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $query = $this->in_vars('q') == null
            ? null
            : Q::contains('name,fullname,profile,url,location', explode(' ', $this->in_vars('q')));
        $this->vars('object_list', C(OpenpearMaintainer)->find_all($paginator, Q::order('name'), $query));
        $paginator->vars('q', $this->in_vars('q'));
        $this->vars('paginator', $paginator);
    }
    /**
     * Downloads
     * 
     * @context OpenpearPackage $package パッケージオブジェクト
     * @context OpenpearRelease[] $object_list 最新リリースオブジェクトの配列
     */
    public function package_downloads($package_name) {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $this->vars('package', $package);
        $this->vars('object_list', C(OpenpearRelease)->find_all(Q::eq('package_id', $package->id()), Q::order('-id')));
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
    public function document_browse($package_name, $path='') {
        $lang = $this->in_vars('lang', Gettext::lang());
        if (empty($path)) {
            $this->redirect_method('document_browse', $package_name, '/'. $lang. '/README');
        }
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $path = rtrim(ltrim($path, ' /.'), '/');
        $root = File::absolute(OpenpearConfig::svn_root(), implode('/', array($package->name(), 'doc')));
        $repo_path = File::absolute($root, $path);
        $this->vars('package', $package);
        try {
            $body = Subversion::cmd('cat', array($repo_path));
        } catch (Exception $e) {
            $body = '';
        }
        if (empty($body)) {
            Http::status_header(404);
            $body = '* Not found.'. PHP_EOL;
            $body .= 'Requested page is not found in our repositories.';
        }
        $this->vars('lang', $lang);
        $this->vars('body', HatenaSyntax::render($body));
        $this->vars('tree', Subversion::cmd('list', array($root. '/'. $lang), array('recursive' => 'recursive')));
        $this->add_vars_other_tree($package_name, 'doc');
    }

    /**
     * ？？？？？
     * @const string $svn_url リポジトリのURL
     */
    public function source_browse($package_name, $path='') {
        if (empty($path)) {
            $this->redirect_method('source_browse', $package_name, '/trunk');
        }
        // TODO 仕様の確認
        // TODO SVNとの連携
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $path = rtrim(ltrim($path, ' /.'), '/');
        $local_root = File::absolute(OpenpearConfig::svn_root(), $package->name());
        $repo_path = File::absolute($local_root, $path);
        $info = Subversion::cmd('info', array($repo_path));
        if ($info['kind'] === 'dir') {
            $this->vars('tree', self::format_tree(Subversion::cmd('list', array($info['url']), array('revision' => $this->in_vars('rev', 'HEAD')))));
        } else if ($info['kind'] === 'file') {
            $this->put_block('package/source_viewfile.html');
            $p = explode('.', $info['path']);
            $ext = array_pop($p);
            if (in_array($ext, $this->allowed_ext)) {
                $source = Subversion::cmd('cat', array($info['url']), array('revision' => $this->in_vars('rev', 'HEAD')));
                $this->vars('code', $source);
                try {
                    $cache_key = array('syntax_highlight', md5($source));
                    if (Store::has($cache_key)) {
                        $this->vars('code', Store::get($cache_key));
                    } else {
                        include_once 'geshi/geshi.php';
                        $geshi = new Geshi($source, $ext);
                        $code = $geshi->parse_code();
                        Store::set($cache_key, $code);
                        $this->vars('code', $code);
                    }
                    $this->vars('geshi', true);
                } catch (Exception $e) {
                    Log::debug($e);
                    $this->vars('geshi', false);
                }
            }
        } else {
            $this->redirect_by_map('package', $package_name);
        }
        $this->vars('path', $path);
        $this->vars('info', self::format_info($info));
        $this->vars('package', $package);
        $this->vars('real_url', File::absolute(OpenpearConfig::svn_url(), implode('/', array($package->name(), $path))));
        $this->vars('externals', Subversion::cmd('propget', array('svn:externals', $info['url'])));
        $this->add_vars_other_tree($package_name);
    }
    /**
     * SVNチェンジセットの詳細
     * @param integer $revision リビジョン番号
     * @context OpenpearPackage $package パッケージ
     * @context OpenpearChangeset $changeset チェンジセット
     * @const string $svn_root　リポジトリのルートパス
     */
    public function changeset($revision) {
        $revision = intval($revision);
        $changeset = C(OpenpearChangeset)->find_get(Q::eq('revision', $revision));
        $package = C(OpenpearPackage)->find_get(Q::eq('id', $changeset->package_id()));
        $path = OpenpearConfig::svn_root();
        $diff = Subversion::cmd('diff', array($path), array('revision' => sprintf('%d:%d', $revision-1, $revision)));
        $revs = array_map('intval', C(OpenpearChangeset)->find_distinct('revision', Q::eq('package_id', $package->id())));
        for ($i=0; $i<count($revs); $i++) {
            if ($revs[$i] == $revision) {
                $this->vars('next_revision', isset($revs[$i+1])? $revs[$i+1]: null);
                $this->vars('prev_revision', isset($revs[$i-1])? $revs[$i-1]: null);
            }
        }
        $this->vars('latest_revision', max($revs));
        $this->vars('revision', $revision);
        $this->vars('package', $package);
        $this->vars('changeset', $changeset);
        $this->vars('diff', $diff);
    }

    private function add_vars_other_tree($package_name, $root='') {
        $trees = array('trunk' => false);
        foreach (array('branches', 'tags') as $path) {
            $rep_path = trim(implode('/', array($root, $path)), '/');
            $list = Subversion::cmd('list', array(implode('/', array(OpenpearConfig::svn_root(), $package_name, $rep_path))));
            if (is_array($list)) foreach ($list as $file) {
                if (isset($file['kind']) && $file['kind'] == 'dir') {
                    $trees[implode('/', array($path, $file['name']))] = false;
                }
            }
        }
        foreach ($trees as $path => $current) {
            if (strpos(Request::current_url(), $path) !== false) {
                $trees[$path] = true;
            }
        }
        $this->vars('other_tree', $trees);
    }

    /**
     * ？？？？
     * @param array $tree
     * @return array
     */
    static public function format_tree(array $tree) {
        // TODO 仕様の確認
        foreach ($tree as &$f) {
            try {
                $log = Subversion::cmd('log', array(OpenpearConfig::svn_root()), array('revision' => $f['commit']['revision'], 'limit' => 1));
                $f['log'] = array_shift($log);
                try {
                    $f['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $f['commit']['author']));
                } catch (Exception $e) {
                    // FIXME
                    $f['maintainer'] = new OpenpearMaintainer();
                }
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        Log::debug($tree);
        return $tree;
    }
    /**
     * SVNからログを取得
     * @param array $info
     * @return array
     */
    static public function format_info(array $info) {
        // TODO 仕様の確認
        // TODO Subversion::cmdの実装
        $log = Subversion::cmd('log', array($info['url']), array('limit' => 1));
        $info['recent'] = array_shift($log);
        try {
            $info['recent']['maintainer'] = C(OpenpearMaintainer)->find_get(Q::eq('name', $info['recent']['author']));
        } catch (NotfoundDaoException $e) {
            // FIXME
            $info['recent']['maintainer'] = new OpenpearMaintainer();
        }
        return $info;
    }

    /**
     * リリースパッケージの ATOM フィード
     **/
    public function packages_releases_atom() {
        Atom::convert(trans('Recent Releases'), url('packages/releases.atom'),
            C(OpenpearRelease)->find_all(new Paginator(20), Q::order('-id'))
        )->output();
    }

    /**
     * パッケージのタイムライン？
     * @param string $package_name パッケージ名
     */
    public function package_timeline($package_name) {
        // TODO　仕様の確認
        Http::redirect(url('package/'. $package_name));
    }

    /**
     * タイムラインをAtomフィードで出力
     */
    public function timeline_atom() {
        // TODO 仕様の確認
        Atom::convert('Openpear Timelines', url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::order('-id'))
        )->output();
    }
    /**
     * ？？？？
     * @param string $package_name パッケージ名
     */
    public function timeline_atom_package($package_name) {
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
    public function timeline_atom_maintainer($maintainer_name) {
        // TODO 仕様の確認
        $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $maintainer_name));
        Atom::convert('Openpear Maintainer Timelines: '. $maintainer->name(), url('timelines.atom'),
            C(OpenpearTimeline)->find_all(new Paginator(20), Q::eq('maintainer_id', $maintainer->id()), Q::order('-id'))
        )->output();
    }

    /**
     * not found (http status 404)
     */
    protected function not_found(Exception $e) {
        Log::debug('404');
        Http::status_header(404);
        $this->output('error/not_found.html');
        exit;
    }

    /**
     * Subversion リポジトリの基本ディレクトリ構成を生成する
     */
    static public function __setup_generate_skeleton__(Request $req) {
        $base_dir = $req->in_vars('path', OpenpearConfig::svn_skeleton(work_path('skeleton')));
        File::mkdir($base_dir);
        File::mkdir(File::path($base_dir, 'doc'));
        File::mkdir(File::path($base_dir, 'doc/en'));
        File::mkdir(File::path($base_dir, 'doc/ja'));
        File::mkdir(File::path($base_dir, 'trunk'));
        File::mkdir(File::path($base_dir, 'tags'));
        File::mkdir(File::path($base_dir, 'branches'));
        File::write(File::path($base_dir, 'doc/ja/README'), text('
            * Documentation
            このパッケージにはまだドキュメントが存在しません
        '));
        File::write(File::path($base_dir, 'doc/en/README'), text('
            * Documentation
            This package does not have any documents.
        '));
    }

    /***
        C(OpenpearMaintainer)->find_all();
        eq(true,true);
     */
}
