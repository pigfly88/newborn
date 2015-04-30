<?php
class controller extends component{
	public $m;
	protected function display($data=array(), $file=''){
		oo::include_file(NFS_BASE_ROOT.'view.php');
		view::display($data, $file);
	}
	
	protected function req($name, $default=null, $callback=null, $type='REQUEST'){
		return oo::base('request')->param($name, $default, array(array($this, $callback)), $type);
	}

    protected function json($array){
    	return oo::base('request')->json($array, 'encode', 1);
    }
   
    /**
     * 动态加载
     * 
     */
    public function __call($c, $args) {
    	if(in_array($c, array('get', 'update', 'delete', 'insert'))){
    		oo::base('controller_auto')->$c($args);
    	}
    }
}