<?php require dirname(__FILE__). '/__settings__.php'; app(); ?>
<app name="Openpear" summary="PEAR Repository Channel and Subversion Hosting Service" ns="Openpear" unmatch_redirect="/">
	<installation>
        # HatenaSyntaxを利用しているのでinstallします
        sudo pear upgrade
        sudo pear channel-discover openpear.org
        sudo pear install openpear/HatenaSyntax-beta
        
        mysqlに resources/schema.sql に流し込んでテーブル作成

        # 基本設定
        php setup.php
            
        # .htaccessを作成してpathinfoをきれいに        
        php setup.php -write_htaccess /openpear
            
        その後、__settings__.php.defaultを参考に__settings__.phpに追記する
	</installation>
    <description>
        http://github.com/nequal/Openpear
        http://groups.google.com/group/openpear-project
    </description>

    <handler error_template="error.html">
        <maps class="org.openpear.flow.parts.Openpear">
            <map method="index" template="index.html" summary="サイトトップ" />
            <map url="search"  method="search" />
            <map url="dashboard" method="dashboard" template="dashboard.html" />
            <map url="dashboard/message/hide" method="dashboard_message_hide" />

            <map url="package/(.+)/doc" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc/(.+)" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc\.(.+?)/(.+)" method="browse_tag" template="package/document.html" />

            <map url="account/login" method="account_login" template="account/login.html" success_redirect="/dashboard" />
            <map url="account/login_openid" method="login_by_openid" success_redirect="/dashboard" />
            <map url="account/signup" method="signup" template="account/signup.html" />
            <map url="account/signup_do" method="signup_do" success_redirect="/dashboard" fail_redirect="/account/signup">
            	<arg name="welcome_mail_template" value="messages/registered.txt" />
            </map>
            <map url="account/logout" method="account_logout" success_redirect="/" />
            
	        <map url="/maintainer/(.+)" method="maintainer_profile" template="maintainer/model.html" />                    
            <map url="maintainers" method="maintainer_search" template="maintainer/models.html" />
            <map url="maintainers/update\.json" method="maintainer_update_json" />
            
            <map url="message/inbox" method="inbox" template="message/inbox.html" />
            <map url="message/sentbox" method="sentbox" template="message/sentbox.html" />
            <map url="message/compose" method="compose" template="message/compose.html" />
            <map url="message/compose/confirm" method="send_confirm" template="message/confirm.html" />
            <map url="message/compose/send" method="send_do" success_redirect="/message/sentbox" />
            <map url="message/(\d+)" method="message" template="message/detail.html" fail_redirect="/message/inbox" />
            
            

            <map url="packages" method="packages" />
            <map url="packages/create" method="package_create" template="package/create.html" />
            <map url="packages/create_do" method="package_create_do" success_redirect="/dashboard" />
            <map url="package/([^/]+)" method="package" template="package/model.html" />
            <map url="package/(.+)/timeline" method="package_timeline" template="package/timeline.html" />
            <map url="package/(.+)/downloads" method="package_downloads" template="package/downloads.html" />
            <map url="package/(.+)/like/(.+)" method="package_add_favorite" />
            <map url="/package(.+)/unlike/(.+)" method="package_remove_favorite" />
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
    
    <handler class="com.tokushimakazutaka.flow.parts.Docs" />
    <handler class="org.rhaco.flow.parts.Crud" />
</app>
