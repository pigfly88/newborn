<?php
class share_m extends model{
	protected $table = 'test.tbl_user';
	//protected $db = 'mongo';
	protected $mongo;
    public function _init(){
    	parent::_init();
    	//$this->mongo = db::driver('mongo');
    	
    	//自动填充
    	$this->auto = array(
    		'select'=>array(
    			'_sort'=>array(
    				'ctime'=>-1,//逆序
    			),
    		),
    		'insert'=>array(
    			'ctime'=>time(),
    		),
    		'update'=>array(
    			'mtime'=>time(),
    		),
    		
    	);
    }
}