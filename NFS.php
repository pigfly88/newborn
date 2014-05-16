<?php
/**
 * NFS框架初始化文件
 *
 * @version        2013 Fri Dec 27 09:50:23 GMT 2013
 * @author         justlikeheaven <328877098@qq.com>
 * @link           https://github.com/justlikeheaven/NFS.git
 */

//开始时间
define('TIME', time());

//文件分隔符
define('DS', DIRECTORY_SEPARATOR);

//NFS框架根目录
define('NFS_ROOT', __DIR__.DS);

define('NFS_BASE_ROOT', NFS_ROOT.'base'.DS);

define('CONTROLLER_ROOT', APP_ROOT.DS.'controller'.DS);
define('MODEL_ROOT', APP_ROOT.DS.'model'.DS);
define('CONFIG_ROOT', APP_ROOT.DS.'config'.DS);
define('VIEW_ROOT', APP_ROOT.DS.'view'.DS);

class NFS{
	protected static $_loaded;
	
	public static $controller;
	public static $action;	
	public static $approot;
	
	public static function load($file){
		if(!isset(self::$_loaded[$file]))
			self::$_loaded[$file] = require($file);

		return self::$_loaded[$file];
	}
	
	public static function loaded($file){
		return true===self::$_loaded[$file] ? true : false;
	}
	
	public static function autoload($class){
		
		$res = false;
		$ext = '.php';
		
		if(false!==strpos($class, 'Controller'))
			$res = self::load(APP_ROOT.DS.'Controller'.DS.$class.$ext);
		else if(false!==strpos($class, 'Model'))
			$res = self::load(APP_ROOT.DS.'Model'.DS.$class.$ext);
            
		return $res;
	}
	
	public static function run(){
		NFS::load(NFS_ROOT.'/base/Common.php');
		NFS::load(NFS_ROOT.'/base/NFSException.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		NFS::load(NFS_ROOT.'/base/Controller.php');
		
		spl_autoload_register(array(self, 'autoload'));
		
		
		self::$controller = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']).'Controller' : 'indexController';
		$action = self::$action = !empty($_REQUEST['a']) ? strtolower($_REQUEST['a']) : 'index';
		$controllerFile = APP_ROOT.DS.'controller'.DS.self::$controller.'.php';
		
		//try{
			$controller = new self::$controller();	
			$controller->$action();
		//}catch (Exception $e){
			//var_dump($e);
		//}
		
	}
	
	
   
   
}



//NFS::load(NFS_ROOT.'/base/Cache.php');
//Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));
