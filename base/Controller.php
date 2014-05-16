<?php
class Controller extends Component{
	
	protected function display($data=array(), $view=''){
		NFS::load(NFS_ROOT.DS.'base'.DS.'View.php');
		View::load($data, $view);
	}
	
	public function loadController($controller){
		$class = $controller.'Controller';		
		$file = CONTROLLER_ROOT.$class.'.php';
		NFS::load($file);
		
		return new $class();
	}
	
	public function loadModel($model=''){
		NFS::load(NFS_ROOT.DS.'base'.DS.'Model.php');
		$model = empty($model) ? substr(NFS::$controller, 0, -10) : $model;
		return Model::loadModel($model);
	}
	
	protected function jump(){
		
	}
	
	public function __call($name, $arguments){
		//self::model()->getAll();
    }
    
    
}