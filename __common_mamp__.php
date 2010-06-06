<?php
Lib::config_path(null,work_path("vendors"));

def("org.yabeken.service.Pea@pear_path",work_path("pear"));
def("org.rhaco.storage.db.Dbc@org.openpear","type=org.rhaco.storage.db.module.DbcMysql,dbname=openpear,user=root,password=root,encode=utf8");

C(Log)->add_module(R('org.rhaco.io.log.LogFile'));
C(Log)->add_module(R('com.tokushimakazutaka.io.log.LogGrowl'));

