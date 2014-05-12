<?php
class Controller extends Component {
	protected static $obj = array();
	
	public function index(){
		echo "Hello, I'm NFS!";
	}
	
	protected function display($data=array(), $view=''){
		NFS::load(NFS_ROOT.DS.'base'.DS.'View.php');
		View::load($data, $view);
	}
	
	public static function load($controller){
		$class = $controller.'Controller';
		if(isset(self::$obj[$class])){
			echo 'static';
			return self::$obj[$class];
		}
		
		$file = CONTROLLER_ROOT.$class.'.php';
		if(!is_file($file)){
			return null;
		}

		NFS::load($file);
		$res = false;
		if($res = new $class()){
			self::$obj[$class] = $res;
		}
		return $res;
	}
	
	public static function model($model=''){
		NFS::load(NFS_ROOT.DS.'base'.DS.'Model.php');
		$model = empty($model) ? substr(NFS::$controller, 0, -10).'Model' : $model.'Model';
		
		return new $model();
	}
	
	protected function jump(){
		
	}
	
	public function __call($name, $arguments){
		self::model()->getAll();
    }
    
    
}