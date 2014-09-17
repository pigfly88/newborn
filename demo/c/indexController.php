<?php
class indexController extends Controller{
	
	//自动调用的初始化方法
	protected function __init(){
		//echo 'calling __init()';
	}
	
	//模板加载示例

	public function index(){
		$this->display();
	}
	
	//模型加载示例
	public function model_load(){
        $res = array();
        
		//自定义模型
		$res = Model::load('user');
		var_dump($res);
        
		//未定义模型
		//$res = Model::load('article')->getAll();
        //var_dump($res);
	}
	
	//控制器调度示例
	public function controller_load(){
		$res = Controller::load('list')->index();
		var_dump($res);
	}
	
    //工具包调度
    public function helper_usage(){
        NFS::helper('Socket')->send('Hi');
    }
    
    //自动完成-表单添加操作-前置方法
    public function nfs_before_user_add(){
    	echo 1;
    }
    
    //自动完成-表单添加操作-后置方法
    public function user_add(){
    	
    }
    
}