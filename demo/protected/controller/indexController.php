<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public static function sayhello(){
		$res = M('list')->getColumn(array('id'=>self::$id), 'deparment_id');
		self::view($res, 'sayHello');
	}
	
}