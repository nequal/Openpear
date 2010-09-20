<?php require dirname(__FILE__). '/__settings__.php'; app(); ?>
<app name="Openpear" summary="PEAR Repository Channel and Subversion Hosting Service" ns="Openpear" nomatch_redirect="/">
	<installation>
        mysqlに resources/schema.sql に流し込んでテーブル作成
        # 基本設定
        php setup.php
        # .htaccessを作成してpathinfoをきれいに
        php setup.php -htaccess /openpear
        
        # アカウントの作成時にgmailでメールを送信するのでアカウントの設定が必須
        def('org.openpear.config.OpenpearConfig@gmail_account','**@gmail.com**','**password**');
        # DBの接続設定が必要
        def("org.rhaco.storage.db.Dbc@org.openpear.model","type=org.rhaco.storage.db.module.DbcMysql,dbname=**openpear**,user=**root**,password=**root**,encode=utf8");

        # Subversion の設定
        svnadmin で リポジトリ作成

        # __settings__.php に以下の設定を自分の環境に合わせて適当に変更して記述
        def('org.openpear.config.OpenpearConfig@svn_root', 'file:///Users/riaf/tmp/optest2');
        def('org.openpear.config.OpenpearConfig@svn_url', 'http://svn.openpear.org');
        def('org.openpear.config.OpenpearConfig@svn_access_file', '/Users/riaf/tmp/optest2/openpear.access');
        def('org.openpear.config.OpenpearConfig@svn_passwd_file', '/Users/riaf/tmp/optest2/openpear.passwd');
        def('jp.nequal.net.Subversion@cmd_path', '/opt/local/bin/svn');

        # Subversion の設定後，新規プロジェクト用のディレクトリ構成を生成する
        php setup.php -generate_skeleton
	</installation>
    <description>
        http://github.com/nequal/Openpear
        http://groups.google.com/group/openpear-project
    </description>

    <handler error_template="error/global.html">
        <module class="org.rhaco.flow.module.HtmlFilter" />
        <maps class="org.openpear.flow.parts.OpenpearNoLogin">
            <map url="/" name="index" template="index.html" />
            <map name="search" />
            
            <map url="account/login" name="do_login" template="account/login.html">
                <arg name="login_redirect" value="dashboard" />
            </map>
            <map url="account/login_openid" name="login_by_openid" template="account/login.html">
                <arg name="login_redirect" value="dashboard" />
            </map>
            <map url="account/signup" name="signup" template="account/signup.html">
            	<arg name="welcome_mail_template" value="messages/registered.txt" />
            	<arg name="success_redirect" value="dashboard" />
            	<arg name="fail_redirect" value="signup" />
            </map>
            
	        <map url="maintainer/(.+)" method="maintainer_profile" template="maintainer/model.html" />                    
            <map url="maintainers" method="maintainers" template="maintainer/models.html" />
            
            <map name="packages" template="package/models.html">
                <arg name="updated" value="package/models_updates.html" />
                <arg name="favored_count" value="package/models_favored.html" />
                <arg name="released_at" value="package/models_released.html" />
            </map>
            <map url="packages/tags" name="packages_tags" template="package/tags.html" />
            <map url="packages/releases\.atom" name="packages_releases_atom" />
            
            <map name="package" url="package/([^/]+)" template="package/model.html" />
            <map url="package/(.+)/timeline" name="package_timeline" template="package/timeline.html" />
            <map url="package/(.+)/downloads" name="package_downloads" template="package/downloads.html" />
            
            <map name="document_browse" url="package/(.+)/doc(/.+)?" template="package/document.html" />
            
            <map name="changeset" url="changeset/(\d+)" template="package/changeset.html" />
            <map name="source_browse" url="package/(.+)/src(/?.+)?" template="package/source.html" />

            <map url="timelines.atom" name="timeline_atom" />
            <map url="package/(.+)/timelines\.atom" name="timeline_atom_package" />
            <map url="maintainer/(.+)/timelines\.atom" name="timeline_atom_maintainer" />
        </maps>
        <maps class="org.openpear.flow.parts.OpenpearLogin">
            <map name="dashboard" template="dashboard.html" />
            <map url="dashboard/message/hide" name="dashboard_message_hide" />

            <map url="account/login" name="do_login" template="account/login.html">
                <arg name="login_redirect" value="dashboard" />
            </map>
            <map url="account/logout" name="do_logout">
                <arg name="logout_redirect" value="index" />
            </map>
            
            <map url="maintainers/update\.json" name="maintainer_update_json" />
            
            <map name="message_inbox" url="message/inbox" template="message/inbox.html" />
            <map name="message_sent" url="message/sent" template="message/sent.html" />
            <map name="message_compose" url="message/compose" template="message/compose.html">
                <arg name="confirm_template" value="message/compose_confirm.html" />
                <arg name="success_redirect" value="message_sent" />
            </map>
            <map url="message/(\d+)" name="message" template="message/detail.html" />
                <arg name="fail_redirect" value="message_inbox" />
            </map>
            
            <map name="package_create" url="packages/create" template="package/create.html" />
            <map url="package/(.+)/like/(.+)" name="package_add_favorite" />
            <map url="package/(.+)/unlike/(.+)" name="package_remove_favorite" />
            <map url="package/(.+)/category/add" name="package_add_tag" />
            <map url="package/(.+)/category/remove" name="package_remove_tag" />
            <map url="package/(.+)/category/prime" name="package_prime_tag" />
            <map name="package_manage" url="package/(.+)/manage" template="package/manage.html" />
            <map url="package/(.+)/manage/edit" name="package_edit" template="package/edit.html" />
            <map url="package/(.+)/manage/edit_do" name="package_edit_do" />
            <map url="package/(.+)/maintainer/add" name="package_add_maintainer" />
            <map url="package/(.+)/maintainer/remove" name="package_remove_maintainer" />
            
            <map url="package/(.+)/manage/release" name="package_release" template="package/release.html">
                <arg name="confirm_template" value="package/release_confirm.html" />
            </map>
            <map url="package/(.+)/manage/release/upload" name="package_release_by_upload" template="package/release_by_upload.html" />
            <map url="package/(.+)/manage/release_done" name="package_release_done" template="message.html" />
        </maps>
        <maps class="org.openpear.flow.parts.OpenpearAPI" url="api">
            <map url="check_repo_exists" name="check_repo_exists" />
        </maps>
    </handler>

    <handler class="com.tokushimakazutaka.flow.parts.Developer" url="dev" hide="both" />
</app>

<!---
 $bwr = test_browser();
 $bwr->do_get(test_map_url("top"));
 meq("夢のような話だ",$bwr->body()); 
-->
