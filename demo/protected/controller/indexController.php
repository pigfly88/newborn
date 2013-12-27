<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public static function sayhello(){

		//DB::connect(C('db.0'));
		$res1 = M('pepsi_code')->getColumn(array('id'=>self::$id), 'code');

		//DB::connect(C('db.0'));
		$res2 = M('pepsi_code')->getColumn(array('id'=>self::$id), 'voucher_id');
		var_dump($res1, $res2);
		
		self::view($res, 'sayHello');
	}
	
}