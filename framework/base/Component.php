<?php
/**
 * 元件
 */
abstract class component{
    
    protected $caller = null;
    public function __construct(){
    	//$this->set_caller();
        method_exists($this, '_init') && $this->_init();
	}
	
	public function index(){
		echo "Hello, I'm NFS!";
	}
	
	public function set_caller(){
		if(function_exists('get_called_class')){
			$this->caller = get_called_class();
			
		}else{
			exit('get_called_class fail');
		}
	}
	
	public function get_caller(){

		return $this->caller;
			
	}
	
	public function __destruct() {
        //var_dump($this);
        //echo '[destructing...]';
   }
	
	
}