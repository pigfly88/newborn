<?php
class listController extends Controller{
	public function cate(){
		$code = $this->loadController('index')->getCode();
		var_dump($code);
		/*
		$indexc = new indexController();
		$code = $indexc->getCode();
		*/
		$code1 = $this->loadController('index')->getz();
		var_dump($code1);
	}
	
	
}