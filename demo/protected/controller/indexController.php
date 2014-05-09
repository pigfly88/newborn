<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public function sayhello(){
		//DB::init(F('db.slave'));
		$res = $this->loadModel('list')->getCode();
		var_dump($res);
		
		//DB::init(F('db.master'));
		$res = M('list')->getColumn(array('id'=>self::$id), 'code');
		var_dump($res);
		
		
		$res = M('list')->getColumn(array('id'=>self::$id), 'code');
		var_dump($res);
		
		//Cache::init('memcache')->set('name', 'zhupp');
		//var_dump($res1, $res2, Cache::init('memcache')->get('name'));
		//Cache::init('memcache')->get('name');
		//Cache::init('memcache')->get('name');
		//Cache::init('redis')->get('name');
		//include CORE_ROOT.'view/sayHello.html';
		$this->view($res);
	}
	
	public function getCode(){
		return M('list')->getColumn(array('id'=>self::$id), 'code');
	}
	public function getz(){
		return M('list')->getColumn(array('id'=>self::$id), 'code');
	}
}