<?php
require dirname(__FILE__). '/__settings__.php';
require dirname(__FILE__). '/__funcs__.php';
app(); ?>
<app name="Openpear">
    <class_module class="Log" module="org.rhaco.io.log.LogFile" />
    <handler>
        <module class="org.openpear.Openpear.module.OpenpearAccountModule" />
        
        <map url="" class="org.openpear.Openpear" method="index" template="index.html" />
        <map url="search$" class="org.openpear.Openpear" method="search" />
        <map url="dashboard$" class="org.openpear.Openpear" method="dashboard" template="dashboard.html" />
        <map url="dashboard/message/hide$" class="org.openpear.Openpear" method="dashboard_message_hide" />
        
        <maps class="org.openpear.Package">
            <map url="packages$" method="models" />
            <map url="packages/create$" method="create" />
            <map url="packages/create_do$" method="create_do" redirect="/dashboard" />
            <!-- package -->
            <map url="package/(.+)$" method="model" template="package/model.html" />
            <map url="package/(.+)/timeline$" method="package_timeline" template="package/timeline.html" />
            <map url="package/(.+)/download$" method="download" template="package/download.html" />
            <map url="package/(.+)/like$" method="add_favorite" />
            <map url="package/(.+)/unlike$" method="remove_favorite" />
            <!-- manage -->
            <map url="package/(.+)/category/add$" method="add_tag" />
            <map url="package/(.+)/category/remove$" method="remove_tag" />
            <map url="package/(.+)/category/prime$" method="prime_tag" />
            <map url="package/(.+)/manage$" method="manage" template="package/manage.html" />
            <map url="package/(.+)/manage/edit$" method="edit" template="package/edit.html" />
            <map url="package/(.+)/manage/edit_do$" method="update_do" />
            <!-- release -->
            <map url="package/(.+)/release$" method="package_release" template="package_release" />
            <map url="package/(.+)/release_confirm$" method="package_release_confirm" />
            <map url="package/(.+)/release_do$" method="package_release_do" />
        </maps>
        <maps class="org.openpear.Document">
            <map url="package/(.+)/doc$" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc/(.+)$" method="browse" template="package/document.html" />
            <map url="package/(.+)/doc\.(.+?)/(.+)$" method="browse_tag" template="package/document.html" />
        </maps>
        <maps class="org.openpear.Source">
            <map url="package/(.+)/src(/?.+)?$" method="browse" />
            <map url="package/(.+)/src\.(.+?)(/?.+)?$" method="browse_tag" />
        </maps>
        <maps class="org.openpear.Maintainer">
            <map url="maintainers$" method="models" template="maintainer/models.html" />
            <map url="maintainer/(.+)$" method="model" template="maintainer/model.html" />
        </maps>
        <maps class="org.openpear.Account">
            <map url="account/login$" method="login" template="account/login.html" redirect="/dashboard" />
            <map url="account/login_openid$" method="login_by_openid" redirect="/dashboard" />
            <map url="account/signup$" method="signup" template="account/signup.html" />
            <map url="account/signup_do$" method="signup_do" success_redirect="/dashboard" fail_redirect="/account/signup" /> 
            <map url="account/logout$" method="logout" redirect="/" />
        </maps>
        <maps class="org.openpear.Message">
            <map url="message/inbox$" method="inbox" template="message/inbox.html" />
            <map url="message/sentbox$" method="sentbox" template="message/sentbox.html" />
            <map url="message/compose$" method="compose" template="message/compose.html" />
            <map url="message/compose/confirm$" method="send_confirm" template="message/confirm.html" />
            <map url="message/compose/send$" method="send_do" redirect="/message/sentbox" />
            <map url="message/(\d+)$" method="model" template="message/detail.html" redirect="/message/inbox" />
        </maps>
        <maps class="org.openpear.Timeline">
            <map url="timelines.atom$" method="atom" />
            <map url="package/(.+)/timelines.atom$" method="atom_package" />
            <map url="maintainer/(.+)/timelines.atom$" method="atom_maintainer" />
        </maps>
    </handler>
</app>