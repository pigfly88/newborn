<?php
abstract class component{
	static function classSelf(){
		//var_dump(debug_backtrace());exit;
		if(function_exists('get_called_class')){
			return get_called_class();			
		}else{
			echo 'get_called_class fail';exit;
		}
	}
	abstract protected function off(array $arr);
}
class a extends component {
	public static function aa(){
		echo self::classSelf()."<br />";
	}
	public function off(array $a){
		
	}
}
class b extends a{
	public static function bb(){
		echo self::classSelf()."<br />";
	}
}
a::aa();
b::bb();
exit;
/*
interface VideoCardInter{  
    function Display();  
    function getName();  
}
class Dmeng implements VideoCardInter {  
    function Display(){  
        echo "调用了帝盟显卡";  
    }  
    function getName(){  
        return "Dmeng VideoCard";  
    }  
}
class Mainboard{  
    var $vc;  
    function run(VideoCardInter $vc){  //定义VideoCardInter接口类型参数，这时并不知道是谁来实现。  
        echo "主板运行显卡！";  
    	$this->vc=$vc;  
        $this->vc->Display();  
        
    }  
}
$conputer=new Mainboard();
//用的时候把实现接口类的名称写进来，（现在是帝盟的显卡，也可以换成别的场家的，只要他们都实现了接口）
$conputer->run(new Dmeng);
exit;
*/
class Controller{
	public function __construct(){
	
	}
	
	public function index(){
		echo 'this is a default function';
	}
	
	public function methodList(){
		return get_class_methods(__CLASS__);
	}
	
	public function loadModule($module){
		NFS::load(NFS_ROOT.'/base/Model.php');
		NFS::load(APP_ROOT."/model/{$module}Model.php");
	}
	
	public function module(){
		if(function_exists('get_called_class')){
			$module = substr(get_called_class(), 0, -10);
			NFS::load(NFS_ROOT.'/base/Model.php');
			NFS::load(APP_ROOT."/model/{$module}Model.php");
		}else{
			echo 'get_called_class fail';exit;
		}
	}
}
?>