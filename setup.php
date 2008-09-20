<?php
/*
 * copy this file onto the application folder. 
 */
ini_set("display_errors","On");
ini_set("display_startup_errors","On");

$isrhaco = false;
$error = "";

function search_rhacopath(){
	$path = str_replace("\\","/",getcwd());
	if(is_file($path."/library/rhaco/Rhaco.php")) return $path."/library/rhaco/";
	$rhacopath = "";
	$pathList = explode("/",$path);
	for($i=0;$i<sizeof($pathList);$i++){
		if(sizeof($pathList) == ($i + 1)){
			$rhacopath .= "rhaco/";
		}else{
			$rhacopath .= $pathList[$i]."/";
		}
	}
	return $rhacopath;
}
if(@include_once("./Rhaco.php")){
	$error = "Please copy onto the project folder and execute it";
}else{
	$rhacopath = "";
	if(file_exists("./__settings__.php")) include_once("./__settings__.php");
	if(defined("RHACO_DIR")) $rhacopath = constant("RHACO_DIR");
	if(!empty($_POST["rhacopath"])) $rhacopath = str_replace("\\","/",$_POST["rhacopath"]);

	if(empty($rhacopath) && isset($_SERVER["argc"]) && $_SERVER["argc"] == 2) $rhacopath = $_SERVER["argv"][1];
	if(!empty($rhacopath) && substr($rhacopath,-1) != "/") $rhacopath .= "/";
	if(empty($_SERVER["HTTP_USER_AGENT"])){
		if(empty($rhacopath)) $rhacopath = search_rhacopath();
		if(empty($_SERVER["HTTP_USER_AGENT"]) && !is_file($rhacopath."Rhaco.php")) print("usage: php setup.php [directory path of 'Rhaco.php']\n");
	}
	if(@include_once($rhacopath."Rhaco.php")){
		Rhaco::import("setup.SetupGenerator");
		Rhaco::constant("CONTEXT_PATH",Rhaco::filepath(dirname(__FILE__)));

		$isrhaco = true;
		$setupGenerator	= new SetupGenerator($rhacopath);
		$error = $setupGenerator->error;
		
		if(!empty($error)) $isrhaco = false;
	}
}
?>
<?php if(!empty($_SERVER["HTTP_USER_AGENT"])): ?>
	<?php if(!$isrhaco): ?>
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>setup</title>
		<style type="text/css">
			body{
				background: #ffffff;
				color: #000000;
				margin: 10px;
				padding: 0;
				font: 13px;
			}
			input{
				margin: 2px;			
				vertical-align: middle;
			}
			input[type=submit]{
				background: #eeeeee;
				color: #222222;
				border: 1px outset #cccccc;
			}
			input:focus{
				background-color:#ffffcc;
			}
		</style>
	</head>
	<body>
	<?php if(!empty($error)): ?>
	<div class="exceptions">
		<?php print($error); ?>
	</div>
	<?php endif; ?>
	
	<?php if(empty($error)): ?>
		<form method="post" name="frm">
			<div>
				directory path of 'Rhaco.php'
			</div>
			<div>
				<input type="text" size="50" name="rhacopath" value="<?php print(search_rhacopath()); ?>" />
				<input type="submit" value="set" />
			</div>
		</form>
	<?php endif; ?>
	</body>
	</html>
	<?php endif; ?>
<?php else: ?>
<?php if(!empty($error)){ print($error); } ?>
<?php endif; ?>