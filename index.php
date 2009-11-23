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
        
        <maps class="org.openpear.Package" url="package">
            <map url="s$" method="models" />
            <map url="s/create$" method="create" />
            <map url="s/create_do$" method="create_do" redirect="/dashboard" />
            <!-- package -->
            <map url="/(.+)$" method="model" template="package/model.html" />
            <map url="/(.+)/timeline$" method="package_timeline" template="package/timeline.html" />
            <map url="/(.+)/download$" method="download" template="package/download.html" />
            <map url="/(.+)/like$" method="add_favorite" />
            <map url="/(.+)/unlike$" method="remove_favorite" />
            <!-- manage -->
            <map url="/(.+)/category/add$" method="add_tag" />
            <map url="/(.+)/category/remove$" method="remove_tag" />
            <map url="/(.+)/category/prime$" method="prime_tag" />
            <map url="/(.+)/manage$" method="manage" template="package/manage.html" />
            <map url="/(.+)/manage/edit$" method="edit" template="package/edit.html" />
            <map url="/(.+)/manage/edit_do$" method="update_do" />
            <!-- release -->
            <map url="/(.+)/release$" method="package_release" template="package_release" />
            <map url="/(.+)/release_confirm$" method="package_release_confirm" />
            <map url="/(.+)/release_do$" method="package_release_do" />
        </maps>
        <maps class="org.openpear.Document" url="package">
            <map url="/(.+)/doc$" method="browse" template="package/document.html" />
            <map url="/(.+)/doc/(.+)$" method="browse" template="package/document.html" />
            <map url="/(.+)/doc\.(.+?)/(.+)$" method="browse_tag" template="package/document.html" />
        </maps>
        <maps class="org.openpear.Source" url="package">
            <map url="/(.+)/src(/?.+)?$" method="browse" />
            <map url="/(.+)/src\.(.+?)(/?.+)?$" method="browse_tag" />
        </maps>
        <maps class="org.openpear.Maintainer" url="maintainer">
            <map url="s$" method="models" template="maintainer/models.html" />
            <map url="/(.+)$" method="model" template="maintainer/model.html" />
        </maps>
        <maps class="org.openpear.Account" url="account">
            <map url="/login$" method="login" template="account/login.html" redirect="/dashboard" />
            <map url="/login_openid$" method="login_by_openid" redirect="/dashboard" />
            <map url="/signup$" method="signup" template="account/signup.html" />
            <map url="/signup_do$" method="signup_do" success_redirect="/dashboard" fail_redirect="/account/signup" /> 
            <map url="/logout$" method="logout" redirect="/" />
        </maps>
        <maps class="org.openpear.Message" url="message">
            <map url="/inbox$" method="inbox" template="message/inbox.html" />
            <map url="/sentbox$" method="sentbox" template="message/sentbox.html" />
            <map url="/compose$" method="compose" template="message/compose.html" />
            <map url="/compose/confirm$" method="send_confirm" template="message/confirm.html" />
            <map url="/compose/send$" method="send_do" redirect="/message/sentbox" />
            <map url="/(\d+)$" method="model" template="message/detail.html" redirect="/message/inbox" />
        </maps>
        <maps class="org.openpear.Timeline">
            <map url="timelines.atom$" method="atom" />
            <map url="package/(.+)/timelines.atom$" method="atom_package" />
            <map url="maintainer/(.+)/timelines.atom$" method="atom_maintainer" />
        </maps>
    </handler>
</app>