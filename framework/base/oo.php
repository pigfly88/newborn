<?php
/**
 * oo对象类
 * 实现对象的实例化，支持单例模式
 * 
 *
 */
class oo extends Component {
	static $obj = null;
	
	//实例化
	public static function obj($class, $arg = null){
        !is_object(self::$obj[$class]) && self::$obj[$class] = new $class(implode(',', $arg));
        return self::$obj[$class];
    }
    
	//model加载
	public static function m($class){
		self::obj($class);
	}
	
	//controller加载
	public static function c($class){
		
	}
}