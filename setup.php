<?php
define('_CORE_URL_','http://rhaco.org/core.tar.gz');
define('_JUMP_URL_','http://rhaco.org/jump.php');

function download_expand($url,$base_dir){
	if(substr($base_dir,-1) != '/') $base_dir .= '/';
	$fp = @gzopen($url,'rb');
	if($fp === false){
		print('install fail. please download '.$url."\n");
		exit;
	}
	$src = null;
	while(!gzeof($fp)) $src .= gzread($fp,4096);
	gzclose($fp);

	$result = array();
	for($pos=0,$vsize=0,$cur='';;){
		$buf = substr($src,$pos,512);
		if(strlen($buf) < 512) break;
		$data = unpack('a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8chksum/a1typeflg/a100linkname/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix',$buf);
		$pos += 512;
		if(!empty($data['name'])){
			$obj = new stdClass();
			$obj->type = (int)$data['typeflg'];
			$obj->path = $data['name'];
			$obj->update = base_convert($data['mtime'],8,10);

			switch($obj->type){
				case 0:
					$obj->size = base_convert($data['size'],8,10);
					$obj->content = substr($src,$pos,$obj->size);
					$pos += (ceil($obj->size / 512) * 512);
					break;
				case 5:
			}
			$result[$obj->path] = $obj;
		}
	}
	foreach($result as $f){
		$out = $base_dir.$f->path;
		switch($f->type){
			case 0:
				$out_dir = dirname($out);
				if(!(is_readable($out_dir) && is_dir($out_dir))){
					$path = $out_dir;
					$dirstack = array();
					while(!is_dir($path) && $path != DIRECTORY_SEPARATOR){
						array_unshift($dirstack,$path);
						$path = dirname($path);
					}
					while($path = array_shift($dirstack)) mkdir($path);
				}
				file_put_contents($out,$f->content,LOCK_EX);
				touch($out,$f->update);
				break;
			case 5:
				if(!(is_readable($out) && is_dir($out))){
					$path = $out;
					$dirstack = array();
					while(!is_dir($path) && $path != DIRECTORY_SEPARATOR){
						array_unshift($dirstack,$path);
						$path = dirname($path);
					}
					while($path = array_shift($dirstack)) mkdir($path);
				}
				break;
		}
	}
}
if(php_sapi_name() != 'cli') exit;
if(ini_get('date.timezone') == '') date_default_timezone_set('Asia/Tokyo');
if('neutral' == mb_language()) mb_language('Japanese');
mb_internal_encoding('UTF-8');
umask(0);
error_reporting(E_ALL|E_STRICT);
ini_set('display_errors','Off');
ini_set('display_startup_errors','Off');
ini_set('html_errors','Off');

function setup_php_error_handler($errno,$errstr,$errfile,$errline){
	if(strpos($errstr,' should be compatible with that of') !== false || strpos($errstr,'Strict Standards') !== false) return true;
	if(strpos($errstr,'Use of undefined constant') !== false && preg_match("/\'(.+?)\'/",$errstr,$m) && class_exists($m[1])) return define($m[1],$m[1]);
	throw new ErrorException($errstr,0,$errno,$errfile,$errline);
}
function setup_php_print($msg,$fmt='1;31'){
	if(empty($msg)){
		print($msg."\n");
	}else{
		print(((php_sapi_name() == 'cli' && substr(PHP_OS,0,3) != 'WIN') ? "\033[".$fmt."m".$msg."\033[0m" : $msg)."\n");
	}
}
set_error_handler('setup_php_error_handler',E_ALL|E_STRICT);

if(file_exists('./__settings__.php')){
	try{
		include_once('./__settings__.php');
	}catch(Exception $e){
		setup_php_print($e->getMessage());
	}
}
if(!class_exists('Object')){
	try{
		$pwd = str_replace("\\","/",getcwd());
		$jump_path = null;
		$args = array();

		if(isset($_SERVER['argv'])){
			$argv = $_SERVER['argv'];
			array_shift($argv);
			if(isset($argv[0]) && $argv[0][0] != '-'){
				$args = implode(' ',$argv);
			}else{
				$size = sizeof($argv);
				for($i=0;$i<$size;$i++){
					if($argv[$i][0] == '-'){
						if(isset($argv[$i+1]) && $argv[$i+1][0] != '-'){
							$args[substr($argv[$i],1)] = $argv[$i+1];
							$i++;
						}else{
							$args[substr($argv[$i],1)] = '';
						}
					}
				}
			}
		}
		if(isset($args['h'])){
			setup_php_print('usage: '.basename(__FILE__).' [-install target_package] [-quick]',null);
			exit;
		}
		if(is_file($jump_path = $pwd.'/jump.php')) @require_once($jump_path);
		if(!class_exists('Object') && is_file($jump_path = $pwd.'/core/jump.php')) @require_once($jump_path);
		if(!class_exists('Object') && !isset($args['quick']) && is_file($jump_path = dirname($pwd).'/core/jump.php')) @require_once($jump_path);
		if(!class_exists('Object')){
			if(isset($args['quick'])){
				$jump_path = $pwd.'/jump.php';
				file_put_contents($jump_path,file_get_contents(constant('_JUMP_URL_')));
				setup_php_print('core installed. `'.$jump_path.'`','1;34');
				include_once($jump_path);
			}else{
				$default_path = $pwd."/core/";
				print('core path['.$default_path.']: ');
				fscanf(STDIN,'%s',$install_path);
				$install_path = trim($install_path);
				$install_path = empty($install_path) ? $default_path : $install_path;
			
				if(!empty($install_path)){
					if(substr($install_path,-1) !== '/') $install_path .= '/';
					if(!is_file($install_path.'jump.php')) download_expand(constant('_CORE_URL_'),$install_path);
					@include_once($install_path.'jump.php');
					$jump_path = $install_path.'jump.php';
					setup_php_print('core installed. `'.$jump_path.'`','1;34');
				}
			}
		}
	}catch(Exception $e){
		setup_php_print($e->getMessage());
	}
	setup_php_print('use `'.$jump_path.'`','35');
}
if(class_exists('Object')){
	Setup::start();
}else{
	setup_php_print('core not found');
}
exit;
