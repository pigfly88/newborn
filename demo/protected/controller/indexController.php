<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public static function sayhello(){
		//DB::init(C('db.0'));
		$res1 = M('list')->getColumn(array('id'=>self::$id), 'code');

		//DB::init(C('db.1'));
		$res2 = M('list')->getColumn(array('id'=>self::$id), 'code');
		//var_dump($res1);
		//Cache::init('memcache')->set('name', 'zhupp');
		//var_dump($res1, $res2, Cache::init('memcache')->get('name'));
		//Cache::init('memcache')->get('name');
		//Cache::init('memcache')->get('name');
		//Cache::init('redis')->get('name');
		//include CORE_ROOT.'view/sayHello.html';
		self::view($res, 'sayHello');
	}
	
}