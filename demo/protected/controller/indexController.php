<?php
class indexController extends Controller{
	public static function sayHello(){
		$res = M('list')->getColumn(array('id'=>4), 'deparment_id');
		self::view($res, 'sayHello');
	}
	
}