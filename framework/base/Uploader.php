<?php
class Uploader{
	public static function upload($name, $dest){
		$tmp = $_FILES['userfile']['tmp_name'];
		if(!is_uploaded_file($tmp)){
			exit('1');
		}
		return move_uploaded_file($tmp, $dest);
	}
	
	
	
	
}