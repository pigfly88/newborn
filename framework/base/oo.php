<?php
/**
 * oo对象类
 * 实现对象的实例化，支持单例模式
 * 
 *
 */
class oo {
	private static $_obj = null;//对象容器
	private static $_f = null;//文件容器
	const SINGLETON_TAG = '_s_';//单例标记
	
    
    /**
     * 实例化对象
     * @param string $class
     * @param string $args
     * @param string $singleton 是否单例
     * @return unknown
     */
    public static function obj($file, $args=null, $singleton=true){
    	$class = substr($file, strrpos($file, DS)+1, strlen($file));
    	
    	$singleton && $prefix = self::SINGLETON_TAG;
    	$k = $prefix.$class;
    	
    	!is_object(self::$_obj[$k]) && require $file.PHP_EXT;
    	
    	if(!$singleton){
    		$newclass = new $class();
    		!is_object(self::$_obj[$k]) && self::$_obj[$k] = $newclass;
    		return $newclass;
    	}else{
    		!is_object(self::$_obj[$k]) && self::$_obj[$k] = new $class();
    		return self::$_obj[$k];
    	}
    }
    
    /**
     * 利用魔术方法的特性动态加载NFS下的类库
     * e.g:
     * NFS::helper('Socket')，helper是未定义的静态方法，那么就会通过__callStatic()去调度helper文件夹的Socket类
     */
    public static function __callStatic($folder, $arg) {
    	if(!is_object(self::$_obj[$arg[0]])){
    		$class = explode('/', $arg[0]);
    		self::f(NFS_ROOT.$folder.DS.$arg[0].PHP_EXT);
    		$arg = null && count($class)>1 && $arg = implode(',', array_slice($class, 1));
    		self::$_obj[$arg[0]] = new $class[count($class)-1]($arg);
    	}
    
    	return self::$_obj[$arg[0]];
    }
    
	//model加载
	public static function m($model){
		self::f(NFS_BASE_ROOT.'Model.php');
		return self::obj(MODEL_ROOT.$model.MODEL_EXT);
	}
	
	//view加载
	public static function v($view){
		self::f(NFS_BASE_ROOT.'View.php');
		return self::obj(MODEL_ROOT.$model.MODEL_EXT);
	}
	
	//controller加载
	public static function c($controller){
		self::f(NFS_BASE_ROOT.'Controller.php');
		return self::obj(CONTROLLER_ROOT.$controller.CONTROLLER_EXT);
	}
	
	//加载文件
	public static function f($file=''){
		if(empty($file)){
			return self::$_f;
		}elseif(!is_file($file)){
			return false;
		}elseif(!isset(self::$_f[$file])){
			return self::$_f[$file] = include($file);
		}
	}
	
}