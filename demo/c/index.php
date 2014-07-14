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
        $res = array();
        
		//自定义模型
		$res = Model::load('index')->getCode();
		var_dump($res);
		
		//未定义模型
		$res = Model::load('tbl_post')->getAll();
		var_dump($res);
	}
	
	//控制器调度示例
	public function controller_dispatch(){
		$res = Controller::load('list')->index();
		var_dump($res);
	}
	
    //工具包调度
    public function helper_usage(){
        NFS::helper('Socket')->send('Hi');
    }
}