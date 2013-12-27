<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public static function sayhello(){
		//var_dump(C('db.2'));
		//DB::connect(C('db.2'));
		
		$res = M('pepsi_code')->getColumn(array('id'=>self::$id), 'code');
		$ss = M('pepsi_code')->getColumn(array('id'=>self::$id), 'voucher_id');
		var_dump($res, $ss);
		self::view($res, 'sayHello');
	}
	
}