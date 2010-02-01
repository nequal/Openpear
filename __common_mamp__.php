<?php
set_include_path('/Applications/MAMP/bin/php5/lib/php/'.PATH_SEPARATOR.get_include_path());

def("org.rhaco.storage.db.Dbc@org.openpear.flow.parts.Openpear","type=org.rhaco.storage.db.module.DbcMysql,dbname=openpear,user=root,password=root,encode=utf8");
def("org.openpear.flow.parts.Openpear@svn_passwd_file",work_path("openpear.passwd"));

//def('org.openpear.flow.parts.Openpear@gmail_account','*****@gmail.com','password');

C(Log)->add_module(R('org.rhaco.io.log.LogFile'));

