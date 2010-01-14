<?php
require dirname(__FILE__). '/__settings__.php';
require dirname(__FILE__). '/__funcs__.php';
app(); ?>
<app name="Openpear" summary="PEAR Repository Channel and Subversion Hosting Service" ns="Openpear" unmatch_redirect="/">
    <description>
        http://github.com/nequal/Openpear
        
        インストール手順:
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
    </description>

    <handler error_template="error.html">
        <module class="org.openpear.Openpear.module.OpenpearAccountModule" />

        <maps class="org.openpear.Openpear">
            <map method="index" template="index.html" summary="サイトトップ" />
            <map url="search"  method="search" />
            <map url="dashboard" method="dashboard" template="dashboard.html" />
            <map url="dashboard/message/hide" method="dashboard_message_hide" />
        </maps>

        <maps class="org.openpear.Package" url="packages">
            <map method="models" />
            <map url="/create" method="create" />
            <map url="/create_do" method="create_do" success_redirect="/dashboard" />
        </maps>
        <maps class="org.openpear.Package" url="package">
            <!-- package -->
            <map url="/([^/]+)" method="model" template="package/model.html" />
            <map url="/(.+)/timeline" method="package_timeline" template="package/timeline.html" />
            <map url="/(.+)/downloads" method="downloads" template="package/downloads.html" />
            <map url="/(.+)/like/(.+)" method="add_favorite" />
            <map url="/(.+)/unlike/(.+)" method="remove_favorite" />
        </maps>
        <maps class="org.openpear.Package" url="package">
            <!-- manage -->
            <map url="/(.+)/category/add" method="add_tag" />
            <map url="/(.+)/category/remove" method="remove_tag" />
            <map url="/(.+)/category/prime" method="prime_tag" />
            <map url="/(.+)/manage" method="manage" template="package/manage.html" />
            <map url="/(.+)/manage/edit" method="edit" template="package/edit.html" />
            <map url="/(.+)/manage/edit_do" method="edit_do" />
        </maps>
        <maps class="org.openpear.Release" url="package">
            <map url="/(.+)/manage/release" method="package_release" template="package/release.html" />
            <map url="/(.+)/manage/release_confirm" method="package_release_confirm" />
            <map url="/(.+)/manage/release_do" method="package_release_do" />
        </maps>
        <maps class="org.openpear.Document" url="package">
            <map url="/(.+)/doc" method="browse" template="package/document.html" />
            <map url="/(.+)/doc/(.+)" method="browse" template="package/document.html" />
            <map url="/(.+)/doc\.(.+?)/(.+)" method="browse_tag" template="package/document.html" />
        </maps>
        <maps class="org.openpear.Source" url="package">
            <map url="/(.+)/changeset/(\d+)" method="changeset" template="package/changeset.html" />
            <map url="/(.+)/src(/?.+)?" method="browse" />
            <map url="/(.+)/src\.(.+?)(/?.+)?" method="browse_tag" />
        </maps>
        
        <map url="/maintainer/(.+)" class="org.openpear.Maintainer" method="model" template="maintainer/model.html" />        
        <maps class="org.openpear.Maintainer" url="maintainers">
            <map method="models" template="maintainer/models.html" />
            <map url="/update\.json" method="update_json" />
        </maps>

        <maps class="org.openpear.Account" url="account">
            <map url="/login" method="account_login" template="account/login.html" success_redirect="/dashboard" />
            <map url="/login_openid" method="login_by_openid" success_redirect="/dashboard" />
            <map url="/signup" method="signup" template="account/signup.html" />
            <map url="/signup_do" method="signup_do" success_redirect="/dashboard" fail_redirect="/account/signup" /> 
            <map url="/logout" method="account_logout" success_redirect="/" />
        </maps>
        <maps class="org.openpear.Message" url="message">
            <map url="/inbox" method="inbox" template="message/inbox.html" />
            <map url="/sentbox" method="sentbox" template="message/sentbox.html" />
            <map url="/compose" method="compose" template="message/compose.html" />
            <map url="/compose/confirm" method="send_confirm" template="message/confirm.html" />
            <map url="/compose/send" method="send_do" success_redirect="/message/sentbox" />
            <map url="/(\d+)" method="model" template="message/detail.html" fail_redirect="/message/inbox" />
        </maps>
        <maps class="org.openpear.Timeline">
            <map url="timelines.atom" method="atom" />
            <map url="package/(.+)/timelines\.atom" method="atom_package" />
            <map url="maintainer/(.+)/timelines\.atom" method="atom_maintainer" />
        </maps>
    </handler>
    
    <handler class="com.tokushimakazutaka.flow.parts.Docs" />
</app>
