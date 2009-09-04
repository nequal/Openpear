<?php
require_once dirname(dirname(__FILE__)). '/__settings__.php';
import('Openpear');

$queue = C(OpenpearNewprojectQueue)->find_get(Q::lt('trial_count', 5), Q::order('id'));
$queue->create();
