<?php
class indexController extends Controller{
	private static $id;
	
	public function __init(){
		self::$id=4;
	}
	
	public function index(){

		
		
		$res = $this->model('index')->getCode();
		var_dump($res);
		$this->display($res);

	}
	
}