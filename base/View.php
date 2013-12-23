<?php
class View extends Component {
	public static function load($view='index', $ext='.html'){
		self::calledClass();
		if(empty($view)){
			
			//var_dump(self::classname());exit;
		}
		NFS::load(VIEW_ROOT.$view.$ext);
	}
	
}
