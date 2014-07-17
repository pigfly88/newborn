<?php
/**
 * 元件
 */
abstract class Component{
    /**
     *
     * @var boolean 开关 true-开，false-关，如果设置为0，类将不执行任何操作直接返回
     */
    protected $on = true;
    
    public function __construct(){
        if(method_exists($this, '__init'))
            $this->__init();
	}
	
	public function index(){
		echo "Hello, I'm NFS!";
	}
	
	protected static function calledClass(){
		if(function_exists('get_called_class'))
			return get_called_class();
		else
			exit('get_called_class fail');
	}
	
	public function __destruct() {
        //var_dump($this);
        //echo 'destructing...';
   }
	
	
}