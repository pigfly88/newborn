<?php
class View extends Component {
	public static function load($var=array(), $view='', $ext='.html'){
		$view = empty($view) ? NFS::$action : $view;
		$data = $var;
		include APP_ROOT.DS.VIEW_FOLDER_NAME.DS.$view.$ext;
	}
	
}
