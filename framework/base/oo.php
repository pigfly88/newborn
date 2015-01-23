<?php
/**
 * oo对象类
 * 实现对象的实例化，支持单例模式
 * 
 *
 */
class oo {
	private static $obj = null;//对象容器
	const SINGLETON_TAG = '_s_';//单例标记
	
	/**
	 * 单例模式
	 */
	public static function singleton($class){
        
    }
    
    /**
     * 实例化对象
     * @param string $class
     * @param string $args
     * @param string $singleton 是否单例
     * @return unknown
     */
    public static function obj($class, $args=null, $singleton=true){
    	$singleton && $prefix = self::SINGLETON_TAG;
    	$k = $prefix.$class;
    	if($singleton){
    		!is_object(self::$obj[$k]) && self::$obj[$k] = new $class();
    		return self::$obj[$k];
    	}else{
    		return new $class();
    	}
    }
    
	//model加载
	public static function m($class){
		self::obj($class);
	}
	
	//controller加载
	public static function c($class){
		
	}
}