<?php
/**
 * Release Queue
 *
 * @var integer $package_id @{"require":true}
 * @var integer $maintainer_id @{"require":true}
 * @var integer $revision
 * @var string $build_path
 * @var text $build_conf @{"require":true}
 * @var text $description
 * @var text $notes
 */
class OpenpearReleaseQueue extends Object
{
    protected $package_id;
    protected $maintainer_id;
    protected $revision;
    protected $build_path;
    protected $build_conf;
    protected $description;
    protected $notes;

    private $working_dir;

    /**
     * パッケージのビルドとリリースを行う
     * @return void
     **/
    public function build() {
        $package = C(OpenpearPackage)->find_get(Q::eq('id', $this->package_id));
        $maintainer = C(OpenpearMaintainer)->find_get(Q::eq('id', $this->maintainer_id));

        $this->init_build_dir(work_path('build/'. $package->name(). '.'. date('YmdHis')));
        foreach (array('desc.txt', 'notes.txt', 'summary.txt') as $filename) {
            File::write($this->build_dir($filename), $package->description());
        }

        if ($package->is_external_repository()) {
            switch ($package->external_repository_type()) {
                case 'Git':
                    $cmd = 'git clone';
                    break;
                case 'Mercurial':
                    $cmd = 'hg clone';
                    break;
                case 'Subversion':
                    $cmd = 'svn export';
                    break;
                default:
                    throw new RuntimeException('unknown repository type');
            }
            $command = new Command(sprintf('%s %s %s', $cmd, escapeshellarg($package->external_repository()), escapeshellarg($this->build_dir('tmp'))));
        } else {
            // Openpear Repository
            $revision = (is_numeric($this->revision) && $this->revision > 0)? intval($this->revision): 'HEAD';
            $repository_path = sprintf('%s/%s/trunk', OpenpearConfig::svn_root(), $package->name());
            $command = new Command(sprintf('svn export -r %s %s %s', $revision, escapeshellarg($repository_path), escapeshellarg($this->build_dir('tmp'))));
        }
        if ($command->stderr()) {
            throw new RuntimeException($command->stderr());
        }
        $build_path = $this->build_dir(implode('/', array('tmp', $this->build_path)));
        if (!File::exist($build_path)) {
            throw new RuntimeException(sprintf('build path is not found: %s', $build_path));
        }
        $mv = new Command(sprintf('mv %s %s', escapeshellarg($build_path), escapeshellarg($this->build_dir('src'))));
        if ($mv->stderr() || !is_dir($this->build_dir('src'))) {
            throw new RuntimeException('src dir is not found');
        }

        // ビルドする
        chdir($this->build_dir());
        $project = PEAR_PackageProjector::singleton()->load($this->build_dir());
        $project->configure($this->build_dir('build.conf'));
        $project->make();
        
        // リリースしたファイルはどこ？
        chdir($this->build_dir('release'));
        foreach(glob('*.tgz') as $filename) {
            $package_file = $this->build_dir('release'). '/'. $filename;
            break;
        }
        if (!file_exists($package_file)) {
            throw new RuntimeException('package file is not exists: '. $package_file);
        }

        // サーバーに追加する
        $cfg = include path('channel.config.php');
        $server = new PEAR_Server2($cfg);
        $server->addPackage($package_file);

        // svn tag
        $build_conf = parse_ini_string($this->build_conf, true);
        $svn = new Command(sprintf('svn copy'
            .' %s/%s/trunk/%s'
            .' %s/%s/tags/%s-%s-%s'
            .' -m "%s (%s-%s) (@%s)"'
            .' --username openpear',
            OpenpearConfig::svn_root(), $package->name(), $this->build_path,
            OpenpearConfig::svn_root(), $package->name(), $build_conf['version']['release_ver'], $build_conf['version']['release_stab'], date('YmdHis'),
            'package released', $build_conf['version']['release_ver'], $build_conf['version']['release_stab'], $maintainer->name()
        ));

        // これ以降はエラーが起きてもドンマイ
        try {
            $release = new OpenpearRelease();
            $release->package_id($package->id());
            $release->maintainer_id($maintainer->id());
            $release->version($build_conf['version']['release_ver']);
            $release->version_stab($build_conf['version']['release_stab']);
            $release->notes($this->notes);
            $release->settings($this->build_conf);
            $release->save();

            $package->latest_release_id($release->id());
            $package->released_at(time());
            $package->save();

            C($release)->commit();
            
            $message_template = new Template();
            $message_template->vars('t', new Templf());
            $message_template->vars('package', $package);
            $message_template->vars('maintainer', $maintainer);
            $message = new OpenpearMessage('type=system');
            $message->maintainer_to_id($maintainer->id());
            $message->subject(trans('{1} package have been released.', $package->name()));
            $message->description($message_template->read('messages/released.txt'));
            $message->save(true);
        } catch(Exception $e) {
            Log::error($e);
        }
    }
    private function init_build_dir($working_dir) {
        $this->working_dir = $working_dir;
        foreach (array('release') as $dirname) {
            File::mkdir($this->build_dir($dirname));
        }
        File::write($this->build_dir('build.conf'), $this->build_conf());
    }
    private function build_dir($path = '') {
        return File::absolute($this->working_dir, $path);
    }
}
