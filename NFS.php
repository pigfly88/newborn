<?php
define('NFS_ROOT', dirname(__FILE__).'/');
define('MODEL_ROOT', APP_ROOT.'/model/');
define('CONTROLLER_ROOT', APP_ROOT.'/controller/');
define('VIEW_ROOT', APP_ROOT.'/view/');

$controllerName = !empty($_REQUEST['c']) ? $_REQUEST['c'].'Controller' : 'indexController';
$actionName = !empty($_REQUEST['a']) ? $_REQUEST['a'] : 'index';
$controllerFile = APP_ROOT."/controller/{$controllerName}.php";

NFS::load(NFS_ROOT.'/base/Component.php');
NFS::load(NFS_ROOT.'/base/NFSException.php');
NFS::load(NFS_ROOT.'/base/Model.php');
NFS::load(NFS_ROOT.'/base/View.php');
NFS::load(NFS_ROOT.'/base/Controller.php');
NFS::load(NFS_ROOT.'/base/DB.php');
NFS::load(NFS_ROOT.'/base/Common.php');

DB::connect(NFS::load(APP_ROOT.'/config/db.php'));

try{
	NFS::load($controllerFile);	
	$controller = new $controllerName();	
	$controller->$actionName();
}catch (Exception $e){
	var_dump($e);
}

class NFS{
	public static function load($file){
		static $loaded;
		$res = false;
		
		if(!isset($loaded[$file])){
			if($res = require($file)){
				$loaded[$file] = true;
			}
		}
		
		return $res;
	}
	
	public static function loaded($file){
		return $loaded[$file];
	}
	
	
	
}