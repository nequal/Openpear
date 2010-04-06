<?php require dirname(__FILE__). '/__settings__.php'; app(); ?>
<app name="Openpear" summary="PEAR Repository Channel and Subversion Hosting Service" ns="Openpear" unmatch_redirect="/">
	<installation>
        mysqlに resources/schema.sql に流し込んでテーブル作成
        # 基本設定
        php setup.php
        # .htaccessを作成してpathinfoをきれいに        
        php setup.php -htaccess /openpear
        
        # アカウントの作成時にgmailでメールを送信するのでアカウントの設定が必須
         def('org.openpear.flow.parts.Openpear@gmail_account','**@gmail.com**','**password**');
        # DBの接続設定が必要
         def("org.rhaco.storage.db.Dbc@org.openpear.flow.parts.Openpear","type=org.rhaco.storage.db.module.DbcMysql,dbname=**openpear**,user=**root**,password=**root**,encode=utf8");

        # Subversion の設定
        svnadmin で リポジトリ作成

        # __settings__.php に以下の設定を自分の環境に合わせて適当に変更して記述
        def('org.openpear.flow.parts.Openpear@svn_root', 'file:///Users/riaf/tmp/optest2');
        def('org.openpear.flow.parts.Openpear@svn_url', 'http://svn.openpear.org');
        def('org.openpear.flow.parts.Openpear@svn_access_file', '/Users/riaf/tmp/optest2/openpear.access');
        def('org.openpear.flow.parts.Openpear@svn_passwd_file', '/Users/riaf/tmp/optest2/openpear.passwd');
        def('jp.nequal.net.Subversion@cmd_path', '/opt/local/bin/svn');

        # Subversion の設定後，新規プロジェクト用のディレクトリ構成を生成する
        php setup.php -generate_skeleton

	</installation>
    <description>
        http://github.com/nequal/Openpear
        http://groups.google.com/group/openpear-project
    </description>

    <handler error_template="error.html">
        <maps class="org.openpear.flow.parts.Openpear">
            <map name="top" method="index" template="index.html" />
            <map url="search"  method="search" />
            <map url="dashboard" method="dashboard" template="dashboard.html" name="dashboard" />
            
            <map url="dashboard/message/hide" method="dashboard_message_hide" />

            <map url="package/(.+)/doc" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc/(.+)" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc\.(.+?)/(.+)" method="browse_tag" template="package/document.html" />

            <map url="account/login" method="do_login" template="account/login.html">
                <arg name="login_redirect" value="dashboard" />
            </map>
            <map url="account/login_openid" method="login_by_openid" template="account/login.html">
                <arg name="success_redirect" value="dashboard" />
            </map>
            <map url="account/signup" method="signup" template="account/signup.html" name="signup">
            	<arg name="welcome_mail_template" value="messages/registered.txt" />
            	<arg name="success_redirect" value="dashboard" />
            	<arg name="fail_redirect" value="signup" />
            </map>
            <map url="account/logout" method="do_logout">
                <arg name="logout_redirect" value="top" />
            </map>
            
	        <map url="maintainer/(.+)" method="maintainer_profile" template="maintainer/model.html" />                    
            <map url="maintainers" method="maintainer_search" template="maintainer/models.html" />
            <map url="maintainers/update\.json" method="maintainer_update_json" />
            
            <map url="message/inbox" method="inbox" template="message/inbox.html" />
            <map url="message/sentbox" method="sentbox" template="message/sentbox.html" name="message_sentbox" />
            <map url="message/compose" method="compose" template="message/compose.html" />
            <map url="message/compose/confirm" method="send_confirm" template="message/confirm.html" />
            <map url="message/compose/send" method="send_do" >
                <arg name="success_redirect" value="message_sentbox" />
            </map>
            <map url="message/(\d+)" method="message" template="message/detail.html" fail_redirect="/message/inbox" />
            
            <map url="packages" method="packages" />
            <map url="packages/create" method="package_create" template="package/create.html">
                <arg name="success_redirect" value="dashboard" />
            </map>
            <map url="package/([^/]+)" method="package" template="package/model.html" />
            <map url="package/(.+)/timeline" method="package_timeline" template="package/timeline.html" />
            <map url="package/(.+)/downloads" method="package_downloads" template="package/downloads.html" />
            <map url="package/(.+)/like/(.+)" method="package_add_favorite" />
            <map url="package/(.+)/unlike/(.+)" method="package_remove_favorite" />
            <map url="package/(.+)/category/add" method="package_add_tag" />
            <map url="package/(.+)/category/remove" method="package_remove_tag" />
            <map url="package/(.+)/category/prime" method="package_prime_tag" />
            <map url="package/(.+)/manage" method="package_manage" template="package/manage.html" />
            <map url="package/(.+)/manage/edit" method="package_edit" template="package/edit.html" />
            <map url="package/(.+)/manage/edit_do" method="package_edit_do" />
            
            <map url="package/(.+)/manage/release" method="package_release" template="package/release.html" />
            <map url="package/(.+)/manage/release_confirm" method="package_release_confirm" />
            <map url="package/(.+)/manage/release_do" method="package_release_do" />
            
            <map url="package/(.+)/changeset/(\d+)" method="changeset" template="package/changeset.html" />
            <map url="package/(.+)/src(/?.+)?" method="source_browse" />
            <map url="package/(.+)/src\.(.+?)(/?.+)?" method="browse_tag" />

            <map url="timelines.atom" method="timeline_atom" />
            <map url="package/(.+)/timelines\.atom" method="timeline_atom_package" />
            <map url="maintainer/(.+)/timelines\.atom" method="timeline_atom_maintainer" />
        </maps>
    </handler>
    
    <handler class="com.tokushimakazutaka.flow.parts.Developer" url="dev" release="false" />
</app>
