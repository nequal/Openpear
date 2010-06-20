<?php
require_once __DIR__. '/__init__.php';

class OpenpearLogin extends Flow
{
    // TODO Source
    protected $allowed_ext = array('php', 'phps', 'html', 'css', 'pl', 'txt', 'js', 'htaccess');
    static protected $__allowed_ext__ = 'type=string[]';

    static protected $__user__ = 'type=OpenpearMaintainer,require=true';
    
    /**
     * @context OpenpearTemplf $ot フィルタ
     */
    protected function __init__() {
        $this->add_module(new OpenpearAccountModule());
        $this->vars('pear_domain', OpenpearConfig::pear_domain('openpear.org'));
        $this->vars('pear_alias', OpenpearConfig::pear_alias('openpear'));
        $this->vars('svn_url', OpenpearConfig::svn_url('http://svn.openpear.org'));
        $this->vars('ot', new OpenpearTemplf($this->user()));
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
                $message->save(true);
                echo 'ok';
            }
        } catch (Exception $e) {
            Log::debug($e);
        }
        exit;
    }
    /**
     * メンテナ情報を更新して結果をjsonで出力
     */
    public function maintainer_update_json() {
        try {
            if (!$this->is_post()) throw new OpenpearException('request method is unsupported');
            $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->user()->id()));
            $maintainer->cp($this->vars());
            $maintainer->save();
            C($maintainer)->commit();
            return Text::output_jsonp(array('status' => 'ok', 'maintainer' => $maintainer));
        } catch (Exception $e) {
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
            if ($message->maintainer_to_id() === $user->id()) {
                $message->unread(false);
                $message->save(true);
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
        if ($this->is_post()) {
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
                        $message->save(true);
                        $this->redirect_by_map("success_redirect");
                        break;
                }
            } catch (Exception $e) {
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
            C($fav)->commit();
        } catch (Exception $e) {
            Log::debug($e);
        }
        $this->redirect_by_map('package_detail', $package_name);
    }
    /**
     * Fav 削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_favorite($package_name) {
        // TODO 仕様の確認
        $user = $this->user();
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            C(OpenpearFavorite)->find_delete(Q::eq('maintainer_id', $user->id()), Q::eq('package_id', $package->id()));
            OpenpearFavorite::recount_favorites($package->id());
            C(OpenpearFavorite)->commit();
        } catch (Exception $e) {
            Log::debug($e);
        }
        $this->redirect_by_map('package_detail', $package_name);
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
                $charge->role('developer');
                $charge->save();
                C($charge)->commit();
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
                $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->in_vars('maintainer_id')));
                $charge = C(OpenpearCharge)->find_get(Q::eq('package_id', $package->id()), Q::eq('maintainer_id', $maintainer->id()));
                $charge->delete();
                C($charge)->commit();
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
        if ($this->is_post() && $this->is_vars('tag_name')) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                // $package->permission($this->user());
                $package->add_tag($this->in_vars('tag_name'), $this->in_vars('prime', false));
                C($package)->commit();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_detail', $package_name);
    }
    /**
     * パッケージからタグの削除
     * @param string $package_name パッケージ名
     */
    public function package_remove_tag($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('tag_id')) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package->remove_tag($this->in_vars('tag_id'));
                C($package)->commit();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_detail', $package_name);
    }
    /**
     * ？？？
     * @param string $package_name パッケージ名
     */
    public function package_prime_tag($package_name) {
        // TODO 仕様の確認
        if ($this->is_post() && $this->is_vars('tag_id')) {
            $user = $this->user();
            try {
                $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
                $package->permission($this->user());
                $package_tag = C(OpenpearPackageTag)->find_get(Q::eq('tag_id', $this->in_vars('tag_id')), Q::eq('package_id', $package->id()));
                $package_tag->prime(true);
                $package_tag->save();
                C($package_tag)->commit();
            } catch (Exception $e) {
                Log::debug($e);
            }
        }
        $this->redirect_by_map('package_detail', $package_name);
    }
    
    /**
     * パッケージ作成
     */
    public function package_create() {
        // TODO 仕様の確認
        $user = $this->user();
        $package = new OpenpearPackage();
        if ($this->is_post()) {
            try {
                $package->cp($this->vars());
                $package->author_id($user->id());
                $package->save();
                $package->add_maintainer($user);
                C($package)->commit();
                $this->redirect_by_map('package_detail', $package->name());
            } catch (Exception $e) {
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
        } catch (Exception $e) {
            Log::debug($e);
            $this->redirect_by_map('package_detail', $package_name);
        }
    }

    /**
     * パッケージ情報更新
     * @param string $package_name パッケージ名
     */
    public function package_edit($package_name)
    {
        // TODO 仕様の確認
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
            $package->permission($this->user());
            $this->vars('object', $package);
            $this->vars('package', $package);
            $this->vars('maintainers', $package->maintainers());
            if (!$this->is_post()) {
                foreach (array('id', 'name', 'description', 'url', 'public_level', 'external_repository', 'external_repository_type', 'license') as $k) {
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
        } catch (Exception $e) {
            Log::debug($e);
        }
        $this->redirect_by_map('package_detail', $package_name);
    }

    /*
    public function update_confirm() {
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('package_id')));
            $package->permission($this->user());
            $package->cp($this->vars());
            $this->vars('packge', $package);
            return $this;
        } catch (Exception $e) {
            Log::debug($e);
        }
        Http::redirect(url());
    }
    */
    /**
     * パッケージ情報更新
     * @param string $package_name パッケージ名
     */
    public function package_edit_do($package_name) {
        // TODO 仕様の確認
        try {
            $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->in_vars('id')));
            $package->permission($this->user());
            $this->vars('name', $package->name());
            $package->cp($this->vars());
            $package->save();
            C($package)->commit();
            $this->redirect_method('package_manage',$package->name());
        } catch (Exception $e) {
            Log::debug($e);
        }
        return $this->update($package_name);
    }
    /**
     * パッケージのリリース
     * @param string $package_name パッケージ名
     * @context integer $package_id パッケージID
     * @context OpenpearPackage $package パッケージ
     */
    public function package_release($package_name) {
        $package = C(OpenpearPackage)->find_get(Q::eq('name', $package_name));
        $package->permission($this->user());
        if ($this->is_post()) {
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
                    C($queue)->commit();
                    $this->redirect_method('package_release_done', $package_name);// FIXME
                } else {
                    $this->vars('action', 'do');
                    $this->put_block($this->map_arg('confirm_template'));
                }
            } catch (Exception $e) {
                Log::debug($e);
            }
        } else {
            $this->vars('revision', $package->recent_changeset());
            $this->vars('package_notes', $package->generate_release_notes());
            $this->cp(new PackageProjectorConfig());
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
        if ($this->is_post() && $this->is_files('package_file')) {
            try {
                $package_file = $this->in_files('package_file');
                $package_file->generate(work_path('upload/'. $package_name. '-'. date('YmdHis'). '.tgz'));
                if (Tag::setof($xml, file_get_contents(sprintf('phar://%s/package.xml', $package_file->fullname())), 'package')) {
                    $uploaded_name = $xml->f('name.value()');
                    $uploaded_channel = $xml->f('channel.value()');
                    if ($uploaded_name != $package->name() || $uploaded_channel != OpenpearConfig::pear_domain('openpear.org')) {
                        throw new OpenpearException('package name or channel');
                    }
                    // TODO: キューの追加
                }
            } catch (Exception $e) {
                Log::debug($e);
                Exceptions::add($e);
            }
        }
    }
    
    /**
     * リリースキュー追加完了通知
     * 
     * @context string $title ページタイトル
     * @context string $message 本文
     */
    public function package_release_done($package_name) {
        $this->vars('title', 'Your queue added!');
        $this->vars('message', HatenaSyntax::render(text('
            * Your queue added!
            
            Wait a mail.
        ')));
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
