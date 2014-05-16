<?php
class indexController extends Controller{
	
	//自动调用的初始化方法
	protected function __init(){
		echo 'calling __init()';
	}
	
	//模板加载示例
	function index(){
		$this->display();
	}
	
	//模型加载示例
	public function model_load(){
		//自定义模型
		$res = $this->loadModel('index')->getCode();
		var_dump($res);
		
		//未定义模型
		$res = $this->loadModel('tbl_post')->getAll();
		var_dump($res);
	}
	
	//控制器调度示例
	public function controller_dispatch(){
		$res = $this->loadController('list')->index();
		var_dump($res);
	}
	
}