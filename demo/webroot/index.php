<?php
/**
 * 项目入口文件
 * 
 * 加载NFS初始化文件，加载配置、基类等等
 *
 */

class A {
	private $db; 	
	public function __init(CLASS2 $cls2){
		return $cls2->get();
		
	}
}
$a = new A();

//Instantiate the reflection object
$ref = new ReflectionClass('A');
print_r($ref->getMethods());exit;
define('APP_ROOT', dirname(__DIR__));

require '../../framework/NFS.php';



NFS::run();


