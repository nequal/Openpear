<?php
chdir(dirname(__FILE__));
require_once dirname(dirname(__FILE__)). '/__init__.php';
Rhaco::import('single_execution');
new singleExecution();
