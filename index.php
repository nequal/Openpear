<?php
require dirname(__FILE__). '/__settings__.php';
require dirname(__FILE__). '/__funcs__.php';
import('Openpear');

try{
    R(Flow)->add_module(R('Openpear.module.OpenpearAccountModule'))->handler(array(
        /** パッケージ関連(global)のマッピング */
        'packages$' => 'class=Openpear.PackageView,method=models',
        'packages/create$' => 'class=Openpear.PackageView,method=create',
        // 'packages/create_confirm$' => 'class=Openpear.PackageView,method=create_confirm',
        'packages/create_do$' => 'class=Openpear.PackageView,method=create_do,redirect=/dashboard',
        
        /** 個別パッケージのマッピング */
        'package/(.+)$$' => 'class=Openpear.PackageView,method=model,template=package/model.html',
        'package/(.+)/timeline$' => 'class=Openpear.TimelineView,method=package_timeline,template=package/timeline.html',
        'package/(.+)/download$' => 'class=Openpear.ReleaseView,method=download,template=package/download.html',
        'package/(.+)/like$' => 'class=Openpear.PackageView,method=add_favorite',
        'package/(.+)/unlike$' => 'class=Openpear.PackageView,method=remove_favorite',
        /** Pacakge Manager */
        'package/(.+)/add_category$' => 'class=Openpear.PackageView,method=add_tag',
        'package/(.+)/remove_category$' => 'class=Openpear.PackageView,method=remove_tag',
        'package/(.+)/prime_category$' => 'class=Openpear.PackageView,method=prime_tag',
        'package/(.+)/manage$' => 'class=Openpear.PackageView,method=manage,template=package/manage.html',
        'package/(.+)/manage/edit$' => 'class=Openpear.PackageView,method=update,template=package/edit.html',
        'package/(.+)/manage/edit_do$' => 'class=Openpear.PackageView,method=update_do',
        /** release */
        'package/(.+)/manage/release$' => 'class=Openpear.ReleaseView,method=package_release,template=package/release.html',
        'package/(.+)/manage/release_confirm$' => 'class=Openpear.ReleaseView,method=package_release_confirm',
        'package/(.+)/manage/release_do$' => 'class=Openpear.ReleaseView,method=package_release_do',
        /** document */
        'package/(.+)/doc$' => 'class=Openpear.DocumentView,method=browse,template=package/document.html',
        'package/(.+)/doc/(.+)$' => 'class=Openpear.DocumentView,method=browse,template=package/document.html',
        'package/(.+)/doc\.(.+?)/(.+)$' => 'class=Openpear.DocumentView,method=browse_tag,template=package/document.html',
        /** sources */
        'package/(.+)/src(/?.+)?$' => 'class=Openpear.SourceView,method=browse',
        'package/(.+)/src\.(.+?)(/?.+)?$' => 'class=Openpear.SourceView,method=browse_tag',
        
        /** メンテナ */
        'maintainers$' => 'class=Openpear.MaintainerView,method=models,template=maintainer/models.html',
        'maintainer/(.+)$' => 'class=Openpear.MaintainerView,method=model,template=maintainer/model.html',
        
        /** アカウント関係のマッピング */
        'account/login$$' => 'class=Openpear.AccountView,method=login,template=account/login.html,redirect=/dashboard',
        'account/login_openid$' => 'class=Openpear.AccountView,method=login_by_openid,redirect=/dashboard',
        'account/signup$' => 'class=Openpear.AccountView,method=signup,template=account/signup.html',
        'account/signup_do$' => 'class=Openpear.AccountView,method=signup_do,success_redirect="/dashboard",fail_redirect="/account/signup"',
        'account/logout$' => 'class=Openpear.AccountView,method=logout,redirect=/',
        
        /** Messages */
        'message/inbox$' => 'class=Openpear.MessageView,method=inbox,template=message/inbox.html',
        'message/sentbox$' => 'class=Openpear.MessageView,method=sentbox,template=message/sentbox.html',
        'message/compose$' => 'class=Openpear.MessageView,method=compose,template=message/compose.html',
        'message/compose/confirm$' => 'class=Openpear.MessageView,method=send_confirm,template=message/confirm.html',
        'message/compose/send$' => 'class=Openpear.MessageView,method=send_do,redirect=/message/sentbox',
        'message/(\d+)$' => 'class=Openpear.MessageView,method=model,template=message/detail.html,redirect=/message/inbox',
        
        /** timelines */
        'timelines.atom$' => 'class=Openpear.TimelineView,method=atom',
        'package/(.+)/timelines.atom$' => 'class=Openpear.TimelineView,method=atom_package',
        'maintainer/(.+)/timelines.atom$' => 'class=Openpear.TimelineView,method=atom_maintainer',
        
        /** APIs */
        
        // トップページ
        '' => 'class=Openpear.OpenpearView,method=index,template=index.html',
        'dashboard$' => 'class=Openpear.OpenpearView,method=dashboard,template=dashboard.html',
        'dashboard/message/hide$' => 'class=Openpear.OpenpearView,method=dashboard_message_hide',
        'search$' => 'class=Openpear.OpenpearView,method=search',
    ))->output();
} catch(Exception $e) {
    // 漏れたエラー
    Log::error($e->getMessage());
    Http::status_header(500);
}
