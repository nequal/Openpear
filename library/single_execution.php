<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class singleExecution
{
    var $key = null;
    var $options = array(
        'begin'          => true,
        'lock_directory' => null,
        'checks_process_existence' => true,
    );

    function singleExecution($options = array())
    {
        $this->options['lock_directory'] = $this->createPath(
            $this->temporaryDirectory(), $this->underscore(__CLASS__));

        $this->options = array_merge($this->options, $options);

        if ($this->options['begin']) {
            $this->lock();
        }
    }

    function createKey()
    {
        foreach (debug_backtrace() as $stack) {
            if ($stack['file'] !== __FILE__) {
                return $stack['file'] . ':' . $stack['line'];
            }
        }
        return __FILE__ . ':' . __LINE__;
    }

    function createPath()
    {
        return join(func_get_args(), DIRECTORY_SEPARATOR);
    }

    function failed()
    {
        exit();
    }

    function isInvalidLock($directory)
    {
        if (!file_exists($directory)) {
            return true;
        }

        if (!is_dir($directory)) {
            return false;
        }

        $pid_file = $this->createPath($directory, 'pid');
        if (!is_file($pid_file)) {
            return false;
        }

        if (!$this->options['checks_process_existence']) {
            return true;
        }

        return $this->processExists(intval(file_get_contents($pid_file)));
    }

    function lock($key = null)
    {
        if (!$key) {
            $key = $this->createKey();
        }

        $this->unlockIfInvalid($key);

        $lock_directory = $this->lockDirectory($key);

        if (!$this->mkdir($lock_directory)) {
            return $this->failed();
        }

        if (singleExecution::loadExtension('pcntl')) {
            $this->key = $key;
            pcntl_signal(SIGTERM, array($this, 'unlockExit'));
            pcntl_signal(SIGHUP,  array($this, 'unlockExit'));
        }

        register_shutdown_function(array($this, 'unlock'), $key);

        $this->registerPid($lock_directory);

        return $this->succeeded();
    }

    function lockDirectory($key)
    {
        return $this->createPath(
            $this->options['lock_directory'], urlencode($key));
    }

    function mkdir($directory)
    {
        $parent = dirname($directory);
        if ($parent === $directory) {
            return true;
        }
        $this->mkdir(dirname($directory));
        return @mkdir($directory);
    }

    function processExists($pid)
    {
        if ($this->loadExtension('posix')) {
            return posix_kill($pid, 0);
        } else if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return intval(exec(sprintf(
                'tasklist /nh /fi "PID eq %s" 2>nul | find /C "%s"',
                $pid, $pid)));
        } else {
            return intval(exec("ps -e|grep '^ *{$pid} '"));
        }
    }

    function registerPid($directory)
    {
        $fp = fopen($this->createPath($directory, 'pid'), 'a');
        if (!$fp) {
            return false;
        }
        fwrite($fp, getmypid());
        fclose($fp);
        return true;
    }

    function succeeded()
    {
        return true;
    }

    function temporaryDirectory()
    {
        if (function_exists('sys_get_temp_dir')) {
            return sys_get_temp_dir();
        } else if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return $this->createPath('c:', 'temp');
        } else {
            return $this->createPath('', 'tmp');
        }
    }

    function underscore($string)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\1', $string));
    }

    function unlinkAll($file)
    {
        if (!is_dir($file)) {
            return unlink($file);
        }

        $d = dir($file);
        while ($entry = $d->read()) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $this->unlinkAll($this->createPath($d->path, $entry));
        }
        $d->close();

        return rmdir($file);
    }

    function unlock($key)
    {
        return $this->unlinkAll($this->lockDirectory($key));
    }

    function unlockExit($signo)
    {
        $this->unlock($this->key);
        exit;
    }

    function unlockIfInvalid($key)
    {
        $common_key = 'common';

        // spin lock - failed if cannot locked in 2 sec
        $common_lock = $this->lockDirectory($common_key);
        for ($i = 0; $i < 200; ++$i) {
            if ($this->mkdir($common_lock)) {
                break;
            }
            usleep(10000);
        }
        if (!is_dir($common_lock)) {
            $this->failed();
        }

        // critical section
        if (!$this->isInvalidLock($this->lockDirectory($key))) {
            $this->unlock($key);
        }

        // unlock
        $this->unlock($common_key);
    }

    /*
     * static methods
     */
    function loadExtension($extension)
    {
        if (extension_loaded($extension)) {
            return true;
        }

        if (defined('PHP_SHLIB_SUFFIX')) {
            $suffix = PHP_SHLIB_SUFFIX;
        } else if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $suffix = 'dll';
        } else {
            $suffix = 'so';
        }

        $prefix = $suffix === 'dll' ? 'php_' : '';
        $libraries = array("{$prefix}{$extension}.{$suffix}");
        $libraries[] = "{$extension}.so";
        $libraries[] = "php_{$extension}.dll";
        $libraries = array_unique($libraries);

        foreach ($libraries as $library) {
            if (@dl($library)) {
                return true;
            }
        }

        return false;
    }
}

if (debug_backtrace()) {
    return;
}

/*
 * sample code
 */
new singleExecution();

// run these code as follows unless another process was running
for ($i = 0; $i < 10; $i++) {
    echo "$i\n";
    flush();
    @ob_flush();
    sleep(1);
}
