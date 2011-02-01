<?php
/**
 * LoginRequired actions
 *
 * @var string[] $allowed_ext
 * @var OpenpearMaintainer $user @{"require":true}
 */
import('org.openpear.pear.PackageProjector');

class OpenpearLogin extends Flow
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
     * ダッシュボード
     * @context OpenpearMaintainer $maintainer ログインしてるメンテナ
     * @context OpenpearCharge[] $my_package_charges
     * @context OpenpearTimeline[] $timelines
     * @context OpenpearFavorite[] $my_favorites
     * @context OpenpearMessage[] $notices
     */
    public function dashboard() {
        $this->vars('maintainer', $this->user());
        $this->vars('my_packages', C(OpenpearPackage)->find_all(Q::in('id', C(OpenpearCharge)->find_sub('package_id', Q::eq('maintainer_id', $this->user()->id()))), Q::order('-updated')));
        $this->vars('timelines', OpenpearTimeline::get_by_maintainer($this->user()));
        $this->vars('fav_packages', C(OpenpearPackage)->find_all(Q::in('id', C(OpenpearFavorite)->find_sub('package_id', Q::eq('maintainer_id', $this->user()->id()))), Q::order('-updated')));
        $this->vars('notices', C(OpenpearMessage)->find_all(Q::eq('maintainer_to_id', $this->user()->id()), Q::eq('type', 'system_notice'), Q::eq('unread', true)));
    }
    /**
     * メッセージを閉じる？ ajaxで使う？
     * @request integer $message_id メッセージID
     */
    public function dashboard_message_hide() {
        // TODO 仕様の確認
        try {
            if ($this->is_post() && $this->is_vars('message_id')) {
                $message = C(OpenpearMessage)->find_get(Q::eq('id', $this->in_vars('message_id')), Q::eq('maintainer_to_id', $this->user()->id()));
                $message->unread(false);
                $message->save();
                echo 'ok';
            }
        } catch (Exception $e) {
            Log::debug($e);
        }
        exit;
    }

    public function maintainer_edit() {
        $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->user()->id()));
        if ($this->is_post() && $this->verify()) {
            try {
                $maintainer->cp($this->vars());
                $maintainer->save();
                $this->redirect_by_map('maintainer_profile', $maintainer->name());
            } catch (Exception $e) {
                Log::info($e);
            }
        }
        $this->cp($maintainer);
        $this->vars('openid_accounts', C(OpenpearOpenidMaintainer)->find_all(Q::eq('maintainer_id', $maintainer->id())));
    }

    public function maintainer_openid_add() {
        if ($openid = OpenpearOpenIDAuth::login($this)) {
            try {
                $openid_maintainer = C(OpenpearOpenidMaintainer)->find_get(Q::eq('url', $openid));
            } catch (NotfoundDaoException $e) {
                $openid_maintainer = new OpenpearOpenidMaintainer();
                $openid_maintainer->maintainer_id($this->user()->id());
                $openid_maintainer->url($openid);
                $openid_maintainer->save();
            }
        }
        $this->redirect_by_map('maintainer_edit', $this->user()->name());
    }

    /**
     * メンテナ情報を更新して結果をjsonで出力
     */
    public function maintainer_update_json() {
        try {
            if (!$this->is_post()) throw new OpenpearException('request method is unsupported');
            if (!$this->verify()) throw new OpenpearException('invalid ticket');
            $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->user()->id()));
            $maintainer->cp($this->vars());
            $maintainer->save();
            return Text::output_jsonp(array('status' => 'ok', 'maintainer' => $maintainer));
        } catch (Exception $e) {
            header('HTTP', true, 400);
            return Text::output_jsonp(array('status' => 'ng', 'error' => $e->getMessage()));
        }
    }
    /**
     * メッセージ詳細
     * @param integer $id メッセージID
     * @context OpenpearMessage $object メッセージ
     */
    public function message($id) {
        $user = $this->user();
        try {
            $message = C(OpenpearMessage)->find_get(Q::eq('id', $id));
            $message->permission($user, true);
            if ($message->maintainer_to_id() == $user->id()) {
                $message->unread(false);
                $message->save();
            }
            $this->vars('object', $message);
        } catch (Exception $e) {
            Log::debug($e);
            $this->redirect_by_map('fail_redirect');
        }
    }
    /**
     * 受信箱
     * @request integer $page ページ番号
     * @context OpenpearMessage[] $object_list メッセージ一覧
     * @context Paginator $paginator ページネータ
     */
    public function message_inbox() {
        if ($this->is_post() && $this->in_vars('action') === 'mark-all-as-read') {
            C(OpenpearMessage)->mark_all_as_read($this->user());
            $this->redirect_self();
        }
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
            $paginator, Q::eq('maintainer_to_id', $this->user()->id()), Q::order('-id')
        ));
        $this->vars('paginator', $paginator);
    }
    /**
     * 送信したメッセージ
     */
    public function message_sent() {
        $user = $this->user();
        $paginator = new Paginator(20, $this->in_vars('page', 1));
        $this->vars('object_list', C(OpenpearMessage)->find_all(
           $paginator, Q::eq('maintainer_from_id', $user->id()), Q::order('-id')
        ));
        $this->vars('paginator', $paginator);
    }
    /**
     * メッセージを送信
     */
    public function message_compose() {
        if ($this->is_post() && $this->verify()) {
            try {
                $to_maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $this->in_vars('to')));
                $message = new OpenpearMessage();
                $message->cp($this->vars());
                $message->maintainer_to_id($to_maintainer->id());
                $message->maintainer_from_id($this->user()->id());
                switch ($this->in_vars('action')) {
                    case 'confirm':
                        $this->put_block($this->map_arg('confirm_template'));
                        $this->vars('confirm', $message);
                        break;
                    case 'do':
                    default:
                        $message->save();
                        $this->redirect_by_map("success_redirect");
                        break;
                }
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                C($message)->rollback();
                Log::debug($e);
            }
        }
    }

    /**
     * Fav 登録
     * @param string $package_name パッケージ名
     */
    public function package_add_favorite($package_name) {
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $fav = new OpenpearFavorite();
            $fav->maintainer_id($user->id());
            $fav->package_id($package->id());
            $fav->save();
        } catch (NotfoundDaoException $e) {
            $this->not_found();
        } catch (Exception $e) {
            C($fav)->rollback();
            Log::debug($e);
        }
        $this->redirect_by_map('package', $package_name);
    }
    /**
     * Fav 削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_favorite($package_name) {
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            C(OpenpearFavorite)->find_delete(Q::eq('maintainer_id', $user->id()), Q::eq('package_id', $package->id()));
            OpenpearFavorite::recount_favorites($package->id());
        } catch (NotfoundDaoException $e) {
            $this->not_found();
        } catch (Exception $e) {
            Log::debug($e);
        }
        $this->redirect_by_map('package', $package_name);
    }
    /**
     * パッケージにメンテナを追加する
     * @param string $package_name 追加するパッケージ名
     * @request string $maintainer_name メンテナ名
     */
    public function package_add_maintainer($package_name) {
        if ($this->is_post() && $this->is_vars('maintainer_name')) {
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('name', $this->in_vars('maintainer_name')));
                $package->permission($this->user());
                $charge = new OpenpearCharge();
                $charge->maintainer_id($maintainer->id());
                $charge->package_id($package->id());
                $charge->role($this->in_vars('role', 'lead'));
                $charge->save();
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_manage', $package_name);
    }

    public function package_update_maintainer($package_name) {
        if ($this->is_post() && $this->is_vars('maintainer_id')) {
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $package->id()), Q::eq('maintainer_id', $this->in_vars('maintainer_id')));
                $charge->role($this->in_vars('role', 'lead'));
                $charge->save();
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_manage', $package_name);
    }

    /**
     * パッケージからメンテナを削除する
     * @param string $package_name パッケージ名
     * @request integer $maintainer_id メンテナID
     */
    public function package_remove_maintainer($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('maintainer_id')) {
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->in_vars('maintainer_id')));
                $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $package->id()), Q::eq('maintainer_id', $maintainer->id()));
                $charge->delete();
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_manage', $package_name);
    }

    /**
     * カテゴリ登録
     * @param string $package_name パッケージ名
     * @request string $tag_name タグ名
     */
    public function package_add_tag($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('tag_name') && $this->verify()) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                // $package->permission($this->user());
                $package->add_tag($this->in_vars('tag_name'), $this->in_vars('prime', false));
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                C($package)->rollback();
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package', $package_name);
    }
    /**
     * パッケージからタグの削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_tag($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('tag_id') && $this->verify()) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package->remove_tag($this->in_vars('tag_id'));
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                C($package)->rollback();
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_manage', $package_name);
    }
    /**
     * ？？？
     * @param string $package_name パッケージ名
     */
    public function package_prime_tag($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('tag_id') && $this->verify()) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package_tag = C(OpenpearPackageTag)->find_get(Q::eq('tag_id', $this->in_vars('tag_id')), Q::eq('package_id', $package->id()));
                $package_tag->prime(true);
                $package_tag->save();
            } catch (NotfoundDaoException $e) {
                $this->not_found();
            } catch (Exception $e) {
                C($package_tag)->rollback();
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_manage', $package_name);
    }

    /**
     * パッケージ作成
     */
    public function package_create() {
        // TODO 仕様の確認
        $user = $this->user();
        $package = new OpenpearPackage();
        if ($this->is_post() && $this->verify()) {
            try {
                $package->cp($this->vars());
                $package->author_id($user->id());
                $package->save();
                $package->add_maintainer($user);
                $this->redirect_by_map('package', $package->name());
            } catch (Exception $e) {
                C($package)->rollback();
                Log::debug($e);
            }
        }
        $this->cp($package);
    }
    /**
     * パッケージ管理
     * @param string $package_name パッケージ名
     */
    public function package_manage($package_name) {
        // TODO 仕様の確認
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            foreach (array('recruite', 'nomaint') as $flag) {
                $this->vars("flag_{$flag}", false);
            }
            foreach ($package->getflags() as $flag) {
                $this->vars("flag_{$flag}", true);
            }
        } catch (Exception $e) {
            Log::debug($e);
            $this->redirect_by_map('package', $package_name);
        }
    }

    /**
     * パッケージ情報更新
     * @param string $package_name パッケージ名
     */
    public function package_edit($package_name)
    {
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            if ($this->is_post() && $this->verify()) {
                try {
                    $this->vars('name', $package->name());
                    $package->cp($this->vars());
                    $package->save();
                    $this->redirect_method('package_manage',$package->name());
                } catch (Exception $e) {
                    Log::debug($e);
                    C($package)->rollback();
                }
            } else {
                $this->cp($package);
                $this->vars('repository_uri_select', $package->is_external_repository() ? 2 : 1);
            }
        } catch (Exception $e) {
            Log::debug($e);
            $this->redirect_by_map('package', $package_name);
        }
    }

    /**
     * パッケージにフラグをたてる
     * @param string $package_name パッケージ名
     **/
    public function package_setflag($package_name) {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if ($this->is_vars('flag')) {
            $package->setflag($this->in_vars('flag'));
            $package->save();
        }
        $this->redirect_by_map('package', $package_name);
    }
    /**
     * パッケージフラグ折る
     * @param string $package_name パッケージ名
     **/
    public function package_rmflag($package_name) {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if ($this->is_vars('flag')) {
            $package->rmflag($this->in_vars('flag'));
            $package->save();
        }
        $this->redirect_by_map('package', $package_name);
    }

    /**
     * パッケージのリリース
     * @param string $package_name パッケージ名
     * @context integer $package_id パッケージID
     * @context OpenpearPackage $package パッケージ
     */
    public function package_release($package_name) {
        $session_key = "_openpear_vars_release_{$package_name}_";
        if ($this->is_sessions($session_key)) {
            foreach ($this->in_sessions($session_key) as $_k_ => $_v_) {
                if (!isset($this->vars[$_k_])) {
                    $this->vars[$_k_] = (get_magic_quotes_gpc() && is_string($_v_)) ? stripslashes($_v_) : $_v_;
                }
            }
        }

        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());

        if ($this->is_post() && $this->verify()) {
            try {
                $build_conf = new PackageProjectorConfig();
                $build_conf->cp($this->vars());
                if ($this->is_vars('extra_conf')) {
                    $build_conf->parse_ini_string($this->in_vars('extra_conf'));
                }
                foreach (C(OpenpearCharge)->find(Q::eq('package_id', $package->id())) as $charge) {
                    $build_conf->maintainer(R(PackageProjectorConfigMaintainer)->set_charge($charge));
                }
                $build_conf->package_package_name($package->name());
                $build_conf->package_channel(OpenpearConfig::pear_domain('openpear.org'));
                if ($build_conf->package_baseinstalldir() == '') {
                    $build_conf->package_baseinstalldir('.');
                }

                if ($this->in_vars('action') == 'do') {
                    $release_queue = new OpenpearReleaseQueue();
                    $release_queue->cp($this->vars());
                    $release_queue->package_id($package->id());
                    $release_queue->maintainer_id($this->user()->id());
                    $release_queue->build_conf($build_conf->get_ini());
                    $release_queue->notes($this->in_vars('package_notes'));

                    $queue = new OpenpearQueue();
                    $queue->type('build');
                    $queue->data(serialize($release_queue));
                    $queue->save();

                    $message = new OpenpearMessage('type=system_notice,mail=false');
                    $message->maintainer_to_id($this->user()->id());
                    $message->subject(trans('リリースキューに追加されました'));
                    $message->description(trans('{1}のリリースを受け付けました。リリースの完了後，メールでお知らせします。', $package->name()));
                    $message->save();

                    $this->redirect_by_map('dashboard');
                } else {
                    $this->sessions($session_key, $_POST);
                    $this->vars('action', 'do');
                    $this->put_block($this->map_arg('confirm_template'));
                }
            } catch (Exception $e) {
                Log::debug($e);
            }
        } else {
            $this->vars('revision', $package->recent_changeset());
            $this->vars('package_notes', $package->generate_release_notes());
            if ($package->is_latest_release_id()) {
                $latest_release = $package->latest_release();
                $config = new PackageProjectorConfig();
                $config->parse_ini_string($latest_release->settings());
                $this->cp($config);
                $this->vars('version_release_ver', $latest_release->version());
            } else {
                $this->cp(new PackageProjectorConfig());
            }
        }
        $this->vars('package', $package);
        $this->vars('package_id', $package->id());
    }

    /**
     * ファイルアップロードからリリース
     * @param string $package_name パッケージ名
     **/
    public function package_release_by_upload($package_name) {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if ($this->is_post() && $this->is_files('package_file') && $this->verify()) {
            try {
                $package_file = $this->in_files('package_file');
                $package_file->generate(work_path('upload/'. $package_name. '-'. date('YmdHis'). '.tgz'));
                if ($package_xml = simplexml_load_file(sprintf('phar://%s/package.xml', $package_file->fullname()))) {
                    if ($package_xml->name != $package->name()) {
                        throw new OpenpearException(Gettext::trans('incorrect package name'));
                    }
                    if ($package_xml->channel != OpenpearConfig::pear_domain('openpear.org')) {
                        $package_xml->channel = OpenpearConfig::pear_domain('openpear.org');
                        $pd = new PharData($package_file->fullname());
                        $pd->addFromString('package.xml', $package_xml->asXML());
                        unset($pd);
                    }
                    $upload_queue = new stdClass;
                    $upload_queue->package_id = $package->id();
                    $upload_queue->package_file = $package_file->fullname();
                    $upload_queue->maintainer_id = $this->user()->id();
                    $queue = new OpenpearQueue('type=upload_release');
                    $queue->data(serialize($upload_queue));
                    $queue->save();

                    $message = new OpenpearMessage('type=system_notice,mail=false');
                    $message->maintainer_to_id($this->user()->id());
                    $message->subject(trans('リリースキューに追加されました'));
                    $message->description(trans('{1}のリリースを受け付けました。リリースの完了後，メールでお知らせします。', $package->name()));
                    $message->save();

                    $this->redirect_by_map('dashboard');
                }
            } catch (Exception $e) {
                Log::debug($e);
                Exceptions::add($e);
            }
        }

        $this->vars('package', $package);
        $this->vars('package_id', $package->id());
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
    
    /***
        C(OpenpearMaintainer)->find_all();
        eq(true,true);
     */
}
