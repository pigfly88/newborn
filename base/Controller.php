<?php
class Controller extends Component {
	static $obj = array();
	public function index(){
		echo "Hello, I'm NFS!";
	}
	
	protected function view($data=array(), $view=''){
		View::load($data, $view);
	}
	
	public static function loadController($controller){
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
	
	public static function loadModel($model){
		$model = empty($model) ? substr(CONTROLLER, 0, -10) : $model;
		return Model::load($model);
	}
	
	protected function jump(){
		
	}
	
}