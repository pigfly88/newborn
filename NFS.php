<?php
define('NFS_ROOT', dirname(__FILE__));

$controllerName = !empty($_REQUEST['c']) ? $_REQUEST['c'].'Controller' : 'indexController';
$actionName = !empty($_REQUEST['a']) ? $_REQUEST['a'] : 'index';
$controllerFile = APP_ROOT."/controller/{$controllerName}.php";

NFS::load(NFS_ROOT.'/base/NFSException.php');
NFS::load(NFS_ROOT.'/base/DB.php');
DB::connect();

try{
	require NFS_ROOT.'/base/Controller.php';
	require $controllerFile;
	
	$controller = new $controllerName();
	
	$controller->$actionName();
}catch (Exception $e){
	var_dump($e);
}

class NFS{
	public static function load($file){
		static $loaded;
		if(!isset($loaded[$file])){
			if(require($file)){
				$loaded[$file] = true;
			}
		}
		return $loaded[$file];
	}
	
	
	
	
	
}