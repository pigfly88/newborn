<?php
class view extends component {
	public static function display($var=array(), $file='', $ext='.html'){
		empty($file) && $file = NFS::$action;
		$data = $var;
		include VIEW_ROOT.$file.$ext;
	}
	
}
