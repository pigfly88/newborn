<?php
class request extends component {
	protected function __init(){
		
	}
	
	/**
	 * 获取$_GET,$_POST,$_REQUEST
	 * @param string $name
	 * @param string $default
	 * @param string $callback
	 * @param string $type
	 * @return string
	 */
	public function param($name, $default=null, $callback=null, $type='REQUEST'){
		eval("\$res = \$_{$type}[{$name}];");
		
		!$res && !is_null($default) && $res = $default;
		
		if( is_array($callback) && !empty($callback) ){
			if( $callback[0] && method_exists($callback[0][0], $callback[0][1]) ){
				echo '2';
				$call = array(array(), array());
				$callback[0] && $call[0] = $callback[0];
				$callback[1] && $call[1] = $callback[1];
				$res = call_user_func_array($call[0], $call[1]);
			}else{
				$res = call_user_func($callback[0][1], $res);
			}
		}
		return $res;
	}
	
	public function json($array, $type='encode', $echo=0){
		$type=='encode' && $res = json_encode($array);
		$type=='decode' && $res = json_decode($array);
		if($echo){
			echo $res;
			exit;
		}else{
			return $res;
		}
	}
}