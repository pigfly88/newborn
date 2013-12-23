<?php
class indexController extends Controller{
	public function index(){
		echo "hello , I'm NFS!";
	}
	public function sayHello() {
		$res = M('list')->getAll(array('id'=>2), 'uid');
		$this->view();
	}
	
}