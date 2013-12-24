<?php
class Controller extends Component {
	
	public function view($data=array(), $view=''){
		View::load($data, $view);
	}
	
	public function jump(){
		
	}
	
}