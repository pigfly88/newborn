<?php
class listController extends Controller{
	public function sayGoodbye() {
		echo 'goodbye!';
	}
	
	public function cate(){
		$this->module()->cate();
	}
	
	
}