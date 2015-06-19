<?php
function p($vars, $die=0){
	$vars = func_get_args();
	if(count($vars)===1)	$vars = $vars[0];
	echo "<pre>";
	var_dump($vars);
	echo "<pre>";
	$die && die();
}

class Func{
	public static function each($arr, $callback){
		if(!is_array($arr) || empty($arr)) return null;
		foreach ($arr as $k=>$v){
			
		}
	}
	
	
}