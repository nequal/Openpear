<?php
/**
 * post-commit に次のように指定
 *
 * /path/to/php /path/to/openpear/bin/commit-hook.php "$REPOS" "$REV"
 */
require_once dirname(dirname(__FILE__)). '/__init__.php';
Rhaco::import('model.RepositoryLog');

$path = isset($argv[1]) ? $argv[1] : Rhaco::constant('SVN_PATH'). '/'. Rhaco::constant('SVN_NAME');

// リビジョン指定してくれないとヤーよ！
$revision = isset($argv[2]) ? $argv[2] : null;
if(!is_numeric($revision) || $revision < 1) exit;

// どのファイルが変更されたの？
$changed = system(sprintf('/usr/bin/svnlook changed -r %d %s', $revision, $path));
$changed = RepositoryLog::parseSvnlookChanged($changed);

// DB 接続するよ
$db = new DbUtil(RepositoryLog::connection());
if(!Variable::istype('DbUtil', $db)) exit;

// 誰がコミットした？
$author = system(sprintf('/usr/bin/svnlook author -r %d %s', $revision, $path));
$author = trim($author);

// 変更されたファイルのパスからパッケージ名を判定するよ
list($packageName) = explode('/', $changed[0]['path']);
$package = $db->get(new Package(), new C(Q::eq(Package::columnName(), $packageName)));

// パッケージが無いなんてことはあり得ないのだけど。
if(!Variable::istype('Package', $package)) exit;

// diff を丸ごと保存してたらそれ SVN じゃねえか感があるからファイル名だけ
$diff = serialize($changed);
// log message
$log = system(sprintf('/usr/bin/svnlook log -r %d %s', $revision, $path));

$date = system(sprintf('/usr/bin/env LANG=en_US.utf-8 /usr/bin/svnlook date -r %d %s', $revision, $path));

$repLog = new RepositoryLog();
$repLog->setRevision($revision);
$repLog->setAuthor($author);
$repLog->setPackage($package->id);
$repLog->setLog(trim($log));
$repLog->setDiff($diff);
$repLog->setDate(substr($date, 0, 25));
$db->insert($repLog);
