<?php
class indexController extends Controller{
	public function index(){
		echo "hello , I'm NFS!";
	}
	public static function sayHello() {
		$res = M('list')->getColumn(array('id'=>4), 'deparment_id');
		$this->view($res);
	}
	
}