<?php
require_once dirname(__FILE__). '/__common__.php';
C(Log)->add_module(R('org.rhaco.io.log.LogFirebug'));
C(Log)->add_module(R('org.rhaco.io.log.LogFile'));

