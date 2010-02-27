<?php
def("org.rhaco.storage.db.Dbc@org.openpear.flow.parts.Openpear","type=org.rhaco.storage.db.module.DbcMysql,dbname=openpear,user=root,password=root,encode=utf8");
def("org.openpear.flow.parts.Openpear@svn_passwd_file",work_path("openpear.passwd"));

C(Log)->add_module(R('org.rhaco.io.log.LogFile'));
C(Log)->add_module(R('com.tokushimakazutaka.io.log.LogGrowl'));
