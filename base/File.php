<?php
class File{
	
	public static function get($file){
		return file_get_contents($file);
	}
	
	
	public static function set($file, $data, $flags=FILE_APPEND, $context=null){
		return file_put_contents($file, $data, $flags, $context);
	}
	
	
	
	
}