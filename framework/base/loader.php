<?php
class loader {
	public function load($class){
		$obj = new $class();
		
	}
}
$a = new A();

//Instantiate the reflection object
$reflector = new ReflectionClass('A');