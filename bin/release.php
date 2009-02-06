<?php
chdir(dirname(__FILE__));
require_once dirname(dirname(__FILE__)). '/__init__.php';
Rhaco::import('single_execution');
Rhaco::import('util.SvnUtil');
Rhaco::import('model.ReleaseQueue');

new singleExecution();

$db = new DbUtil(ReleaseQueue::connection());
$queue = $db->get(new ReleaseQueue(), new C(Q::order(ReleaseQueue::columnCreated())));

if(Variable::istype('ReleaseQueue', $queue) && $package = $db->get(new Package($queue->package))){

}
