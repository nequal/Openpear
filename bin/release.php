<?php
require_once __DIR__. '/__init__.php';
chdir(__DIR__);

// import pear
require_once 'PEAR/PackageProjector.php';
require_once 'PEAR/Server2.php';

// import libs
import('org.openpear.config.OpenpearConfig');
import('org.openpear.pear.PackageProjector');
import('jp.nequal.net.Subversion');
import('org.openpear.model.OpenpearMaintainer');
import('org.openpear.model.OpenpearPackage');
import('org.openpear.model.OpenpearMessage');
import('org.openpear.model.OpenpearQueue');
import('org.openpear.model.OpenpearRelease');
import('org.openpear.model.OpenpearReleaseQueue');

foreach (C(OpenpearQueue)->find_all(new Paginator(5), Q::lt('locked', time()), Q::eq('type', 'build'), Q::order('updated')) as $queue) {
    try {
        $queue->start(300);
        $release_queue = $queue->fm_data();
        if ($release_queue instanceof OpenpearReleaseQueue === false) {
            throw new RuntimeException('queue data is broken');
        }
        $release_queue->build();
        $queue->delete(true);
    } catch (Exception $e) {
        echo 'what!?';
        Log::error($e);
    }
}

