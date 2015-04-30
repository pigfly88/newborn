<?php
class index_c extends controller{
	
	//自动调用的初始化方法
	protected function __init(){
		//echo 'calling __init()';
	}
	
	//模板加载示例

	public function index(){
		
		oo::c('list')->index();//控制器调度
		oo::m()->get();//模型调度
		$this->display(array('name'=>'zhupp'));//模板渲染
	}
	
	
    
    //自动完成-表单添加操作-前置方法
    public function nfs_before_user_add(){
    	echo 1;
    }
    
    //自动完成-表单添加操作-后置方法
    public function user_add(){
    	
    }
    
}