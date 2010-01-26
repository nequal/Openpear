<?php
set_include_path('/Applications/MAMP/bin/php5/lib/php/'.PATH_SEPARATOR.get_include_path());

def("org.rhaco.storage.db.Dbc@org.openpear.flow.parts.Openpear","type=org.rhaco.storage.db.module.DbcMysql,dbname=openpear,user=root,password=root,encode=utf8");

C(Log)->add_module(R('org.rhaco.io.log.LogFile'));

