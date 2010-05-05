<?php
/**
 * ErrorException と PEAR の相性が悪すぎるから rhaco が使えない
 */
date_default_timezone_set('Asia/Tokyo');
chdir(dirname(__FILE__));
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
require_once 'PEAR/PackageProjector.php';
require_once 'PEAR/Server2.php';

$config = array(
    'db_dsn' => 'mysql:host=localhost;dbname=openpear2',
    'db_user' => 'root',
    'db_pass' => 'root',
    'svn_root' => 'file:///Users/riaf/tmp/optest2',
);

$app_dir = dirname(dirname(__FILE__));
$work_dir = $app_dir. '/work';

$pdo = new PDO($config['db_dsn'], $config['db_user'], $config['db_pass']);
foreach($pdo->query('SELECT * FROM `openpear_release_queue` orq WHERE orq.trial_count < 5 ORDER BY orq.id;') as $row) {
    try {
        // キューの回数をカウントアップ
        $update = $pdo->prepare('UPDATE `openpear_release_queue` SET `trial_count` = ? WHERE `id` = ?');
        $update->execute(array($row['trial_count'] + 1, $row['id']));
        
        // パッケージ情報を取得
        $select = $pdo->prepare('SELECT * FROM `openpear_package` WHERE `id` = ?');
        $select->execute(array($row['package_id']));
        $package = $select->fetch(PDO::FETCH_ASSOC);
        
        // メンテナ情報を取得
        $select = $pdo->prepare('SELECT * FROM `openpear_maintainer` WHERE `id`=?');
        $select->execute(array($row['maintainer_id']));
        $maintainer = $select->fetch(PDO::FETCH_ASSOC);
        
        // ディレクトリを準備
        $working_path = sprintf('%s/build/%s.%s', $work_dir, $package['name'], date('YmdHis'));
        $src_dir = $working_path. '/src';
        $release_dir = $working_path. '/release';
        $conf_path = $working_path. '/build.conf';
        init_dir($working_path);
        file_put_contents($conf_path, $row['build_conf']);
        file_put_contents($working_path. '/desc.txt', $package['description']);
        file_put_contents($working_path. '/notes.txt', $package['description']);
        file_put_contents($working_path. '/summary.txt', $package['description']);
        
        // ソースコードをとってくる
        if (empty($package['external_repository'])) {
            // Openpear Repository
            $repository_path = sprintf('%s/%s/trunk/%s', $config['svn_root'], $package['name'], $row['build_path']);
            list(, $out, $err) = cmd(sprintf('svn export %s %s', $repository_path, $src_dir));
        } else {
            throw new RuntimeException('まだです');
            switch ($package['external_repository_type']) {
                case 'Git':
                case 'Mercurial':
                case 'Subversion':
            }
        }
        
        // ビルドする
        $project = PEAR_PackageProjector::singleton()->load($working_path);
        $project->configure($conf_path);
        $project->make();
        
        // リリースしたファイルはどこ？
        chdir($release_dir);
        foreach(glob('*.tgz') as $filename) {
            $package_file = $release_dir. '/'. $filename;
            break;
        }
        
        // サーバーに追加する
        $cfg = include $app_dir. '/channel.config.php';
        $server = new PEAR_Server2($cfg);
        $server->addPackage($package_file);
        
        // キューを削除する
        $delete = $pdo->prepare('DELETE FROM `openpear_release_queue` WHERE id=?;');
        $delete->execute(array($row['id']));
        
        // リリースログ
        $build_conf = parse_ini_file($conf_path, true);
        $release = $pdo->prepare('INSERT INTO `openpear_release` (`package_id`, `maintainer_id`, `version`, `version_stab`, `notes`, `settings`, `created`) VALUES (:package_id, :maintainer_id, :version, :version_stab, :notes, :settings, NOW());');
        $release->bindValue(':package_id', $row['package_id']);
        $release->bindValue(':maintainer_id', $row['maintainer_id']);
        $release->bindValue(':version', $build_conf['version']['release_ver']);
        $release->bindValue(':version_stab', $build_conf['version']['release_stab']);
        $release->bindValue(':notes', file_get_contents($working_path. '/notes.txt'));
        $release->bindValue(':settings', $row['build_conf']);
        $release->execute();
        
        // svn tag
        cmd(sprintf('svn copy'
            .' %s/%s/trunk/%s'
            .' %s/%s/tags/%s-%s-%s'
            .' -m "%s (%s-%s) (@%s)"'
            .' --username openpear',
            $config['svn_root'], $package['name'], $row['build_path'],
            $config['svn_root'], $package['name'], $build_conf['version']['release_ver'], $build_conf['version']['release_stab'], date('YmdHis'),
            'package released', $build_conf['version']['release_ver'], $build_conf['version']['release_stab'], $maintainer['name']
        ));
        
        // TODO: メッセージキュー追加
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function init_dir($path) {
    if (!is_dir(dirname($path))) {
        throw new RuntimeException('build dir is not found');
    }
    mkdir($path);
    mkdir($path. '/release');
}
function cmd($command) {
    $proc = proc_open($command, array(array("pipe", "r"), array("pipe", "w"), array("pipe", "w")), $resource);
    $stdout = $stderr = '';
    if (isset($resource[0])) fclose($resource[0]);
    if (isset($resource[1])) {
        while (!feof($resource[1])) $stdout .= fgets($resource[1]);
        fclose($resource[1]);
    }
    if (isset($resource[2])) {
        while (!feof($resource[2])) $stderr .= fgets($resource[2]);
        fclose($resource[2]);
    }
    $end_code = fclose($proc);
    return array($end_code, $stdout, $stderr);
}
