<?php
class controller_auto extends controller {
	public function __call($name, $args){
		$m = oo::m(NFS::$controller)->$name($args);
		p($m);
		//p($name, $args);
	}
	
}