<?php
class Controller extends Component {
	public function __construct(){
	
	}
	
	public function index(){
		echo 'this is a default function';
	}
	
	public function methodList(){
		return get_class_methods(__CLASS__);
	}
	
	public function view($view=''){

		$view = empty($view) ? substr(self::calledClass(), 0, -10) : $view;
		View::load($view);
	}
	
	
	
}