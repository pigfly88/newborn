<?php
/**
 * NFS
 *
 * @version        2013 Fri Dec 27 09:50:23 GMT 2013
 * @author         Barry <328877098@qq.com>
 * @link           https://github.com/justlikeheaven/NFS.git
 */
define('TIME', time());
define('NFS_ROOT', dirname(__FILE__).'/');
define('PROTECT_FOLDER', 'protected');
define('CORE_ROOT', APP_ROOT.PROTECT_FOLDER.'/');
define('MODEL_ROOT', CORE_ROOT.'model/');
define('CONTROLLER_ROOT', CORE_ROOT.'controller/');
define('VIEW_ROOT', CORE_ROOT.'view/');
define('CONFIG_ROOT', CORE_ROOT.'config/');


class NFS{
	static $loaded;
	
	public static function load($file){		
		$res = false;
		
		if(!isset(self::$loaded[$file])){
			if(is_file($file) && $res = require($file)){
				self::$loaded[$file] = true;
			}
		}
		
		return $res;
	}
	
	public static function loaded($file){
		return true===self::$loaded[$file] ? true : false;
	}
	
	public static function autoload($className){
		$basePath = NFS_ROOT.'base/';
		$helperPath = NFS_ROOT.'helper/';
		$ext = '.php';

		$res = false;
		
		if(true!==self::$loaded[$basePath.$className.$ext])
			$res = self::load($basePath.$className.$ext);
		
		if(true!==self::$loaded[$helperPath.$className.$ext])
			$res = self::load($helperPath.$className.$ext);
			
		if(true!==self::$loaded[CONTROLLER_ROOT.$className.$ext]){
			$res = self::load(CONTROLLER_ROOT.$className.$ext);
		}
		return $res;
	}
	
}

spl_autoload_register(array('NFS', 'autoload'));

NFS::load(NFS_ROOT.'/base/Common.php');
NFS::load(NFS_ROOT.'/base/NFSException.php');
NFS::load(NFS_ROOT.'/base/Component.php');
NFS::load(NFS_ROOT.'/base/Model.php');
NFS::load(NFS_ROOT.'/base/Controller.php');

NFS::load(NFS_ROOT.'/base/DB.php');
DB::init(NFS::load(CORE_ROOT.'/config/db.php'));

NFS::load(NFS_ROOT.'/base/Cache.php');
Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));

$controllerName = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']).'Controller' : 'indexController';
$actionName = !empty($_REQUEST['a']) ? strtolower($_REQUEST['a']) : 'index';
define('CONTROLLER', $controllerName);
define('ACTION', $actionName);

$controllerFile = CORE_ROOT."/controller/{$controllerName}.php";
try{
	NFS::load($controllerFile);	
	$controller = new $controllerName();	
	$controller->$actionName();
}catch (Exception $e){
	var_dump($e);
}