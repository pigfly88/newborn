<?php
class log{
	protected static $tmp;
	public static function add($data){
		self::$tmp[] = $data;
	}
	
	public static function save(){
		var_dump(self::$tmp);
	}
	
	public function __destruct(){
		echo 'des';
	}
}


log::add(1);
log::add(2);
log::add(3);
log::save();