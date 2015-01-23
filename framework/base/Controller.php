<?php
class Controller extends Component{
	
	protected function display($data=array(), $view=''){
		oo::load(NFS_BASE_ROOT.'View.php');
		View::load($data, $view);
	}
	
	public function load($controller){
		$class = $controller.'Controller';		
		$file = CONTROLLER_ROOT.$class.'.php';
		NFS::load($file);
		
		return new $class();
	}
	
	protected function jump(){
		
	}
	
	public function __call($name, $arguments){
		//self::model()->getAll();
    }
    
    public function json($arr){
    	echo json_encode($arr);
    }
    
    public function add(){
    	$c = substr($this->caller, 0, CONTROLLER_EXT);
    }
    
}