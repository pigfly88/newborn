<?php
class View extends Component {
	public static function load($var=array(), $view='', $ext='.html'){
		$view = empty($view) ? ACTION : $view;
		$data = $var;
		var_dump($data);exit;
		include VIEW_ROOT.$view.$ext;
	}
	
}
