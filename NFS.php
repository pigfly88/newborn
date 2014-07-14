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

//PHP文件后缀
define('PHP_EXT', '.php');

//Controller文件修饰符
define('CONTROLLER_EXT', 'Controller');

//Model文件修饰符
define('MODEL_EXT', 'Model');

//NFS框架根目录
define('NFS_ROOT', __DIR__.DS);
define('NFS_BASE_ROOT', NFS_ROOT.'base'.DS);

define('CONTROLLER_FOLDER_NAME', 'c');
define('MODEL_FOLDER_NAME', 'm');
define('VIEW_FOLDER_NAME', 'v');
define('CONFIG_FOLDER_NAME', 'cfg');

define('CONTROLLER_ROOT', APP_ROOT.DS.CONTROLLER_FOLDER_NAME.DS);
define('MODEL_ROOT', APP_ROOT.DS.MODEL_FOLDER_NAME.DS);
define('CONFIG_ROOT', APP_ROOT.DS.CONFIG_FOLDER_NAME.DS);
define('VIEW_ROOT', APP_ROOT.DS.VIEW_FOLDER_NAME.DS);

class NFS{
	protected static $_loaded;
	
	public static $controller;
	public static $action;	
	public static $approot;
	
    protected static $_obj;
    
    //加载文件
    public static function load($file){
		return !isset(self::$_loaded[$file]) && self::$_loaded[$file] = require($file);
	}
	
    //这个文件加载过了吗？
	public static function loaded($file){
		return true===self::$_loaded[$file] ? true : false;
	}
	
	public static function autoload($class){
		
		$res = false;
		
		if(false!==strpos($class, 'Controller'))
			$res = self::load(APP_ROOT.DS.CONTROLLER_NAME.DS.$class.$ext);
		else if(false!==strpos($class, 'Model'))
			$res = self::load(APP_ROOT.DS.'Model'.DS.$class.PHP_EXT);
            
		return $res;
	}
	
    public static function obj($obj, $arg = null){
        !is_object(self::$_obj[$obj]) && self::$_obj[$obj] = new $obj(implode(',', $arg));
        return self::$_obj[$obj];
    }
    
	public static function run(){
		NFS::load(NFS_ROOT.'/base/Common.php');
		NFS::load(NFS_ROOT.'/base/NFSException.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		NFS::load(NFS_ROOT.'/base/Controller.php');
		
		//spl_autoload_register(array(self, 'autoload'));
		
		
		self::$controller = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']).'Controller' : 'indexController';
		$action = self::$action = !empty($_REQUEST['a']) ? strtolower($_REQUEST['a']) : 'index';
		$controllerFile = APP_ROOT.DS.CONTROLLER_FOLDER_NAME.DS.PHP_EXT;
		require_once $controllerFile;
		try{
			$controller = new self::$controller();	
			$controller->$action();
		}catch (Exception $e){
			var_dump($e);
		}
		
	}
	
    /**
     * 利用魔术方法的特性动态加载NFS下的类库
     * e.g:
     * NFS::helper('Socket')，helper是未定义的静态方法，那么就会通过__callStatic()去调度helper文件夹的Socket类
     */
	public static function __callStatic($folder, $class) {
        if( !is_object( self::$_obj[$class[0]] ) ){
            self::load(NFS_ROOT.$folder.DS.$class[0].PHP_EXT);
            $arg = null && count($class)>1 && $arg = implode( ',', array_slice( $class, 1 ) );
            self::$_obj[$class[0]] = new $class[0]($arg);
        }
        
        return self::$_obj[$class[0]];
    }
   
   
}



//NFS::load(NFS_ROOT.'/base/Cache.php');
//Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));
