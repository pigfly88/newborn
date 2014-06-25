<?php
class Controller extends Component{
	
	protected function display($data=array(), $view=''){
		NFS::load(NFS_ROOT.DS.'base'.DS.'View.php');
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
    
    
}