<?php
class Controller extends Component {
	public function index(){
		echo "Hello, I'm NFS!";
	}
	
	public static function view($data=array(), $view=''){
		View::load($data, $view);
	}
	
	public function jump(){
		
	}
	
}