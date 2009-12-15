<?php
require_once dirname(dirname(__FILE__)). '/__settings__.php';
import('org.openpear.Openpear.OpenpearFlow');
import('org.openpear.Package');

$queue = C(OpenpearNewprojectQueue)->find_get(Q::lt('trial_count', 5), Q::order('id'));
$queue->create();
