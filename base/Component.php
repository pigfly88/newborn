<?php
abstract class Component{
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
       var_dump($this);echo 'destructing...';
   }
	
	
}