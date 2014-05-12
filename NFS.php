<?php
/**
 * NFS框架初始化文件
 *
 * @version        2013 Fri Dec 27 09:50:23 GMT 2013
 * @author         justlikeheaven <328877098@qq.com>
 * @link           https://github.com/justlikeheaven/NFS.git
 */

//NFS框架根目录
define('NFS_ROOT', __DIR__);

//开始时间
define('TIME', time());

//文件分隔符
define('DS', DIRECTORY_SEPARATOR);

class NFS{
	protected static $_loaded;
	
	public static $controller;
	public static $action;	
	public static $approot;
	
	public static function load($file){
		$res = true;
		
		if(!isset(self::$_loaded[$file])){
			if(is_file($file) && $res = require($file)){
				self::$_loaded[$file] = true;
			}
		}
		
		return $res;
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
		
		/**
		 * 当调用到不存在的控制器时，智能调用方法，方便应付一些简单的功能，这样就不需要编写控制器了。
		 */
		if(!file_exists($controllerFile)){
			//$c = new Controller();
			//$c->$action();
		}else{
			//try{
				$controller = new self::$controller();	
				$controller->$action();
			//}catch (Exception $e){
				var_dump($e);
			//}
		}
	}
	
	
   
   
}



//NFS::load(NFS_ROOT.'/base/Cache.php');
//Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));
