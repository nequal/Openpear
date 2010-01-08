<?php
define("_CORE_URL_","http://rhaco.googlecode.com/files/core.tar.gz");

function download_expand($url,$base_dir){
	if(substr($base_dir,-1) != "/") $base_dir .= "/";
	$fp = @gzopen($url,"rb");
	if($fp === false){
		print("install fail. please download ".$url."\n");
		exit;
	}
	$src = null;
	while(!gzeof($fp)) $src .= gzread($fp,4096);
	gzclose($fp);

	$result = array();
	for($pos=0,$vsize=0,$cur="";;){
		$buf = substr($src,$pos,512);
		if(strlen($buf) < 512) break;
		$data = unpack("a100name/a8mode/a8uid/a8gid/a12size/a12mtime/a8chksum/a1typeflg/a100linkname/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor/a155prefix",$buf);
		$pos += 512;
		if(!empty($data["name"])){
			$obj = new stdClass();
			$obj->type = (int)$data["typeflg"];
			$obj->path = $data["name"];
			$obj->update = base_convert($data["mtime"],8,10);

			switch($obj->type){
				case 0:
					$obj->size = base_convert($data["size"],8,10);
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

ini_set("display_errors","On");
ini_set("display_startup_errors","On");
ini_set("html_errors","Off");

if(file_exists("./__settings__.php")) @include_once("./__settings__.php");
if(!class_exists("Object")){
	$default_path = str_replace("\\","/",getcwd())."/"."core/";
	print("core path[".$default_path."]: ");
	fscanf(STDIN,"%s",$install_path);
	$install_path = trim($install_path);
	$install_path = empty($install_path) ? $default_path : $install_path;

	if(!empty($install_path)){
		if(substr($install_path,-1) !== "/") $install_path .= "/";
		if(!is_file($install_path."jump.php")) download_expand(constant("_CORE_URL_"),$install_path);
		@include_once($install_path."jump.php");
	}
}
if(class_exists("Object")) Setup::start();
exit;
