<?php
class user_m extends model{
	protected $table = 'newborn.user';
	protected $mongo;
    public function _init(){
    	parent::_init();
    	$this->mongo = oo::cache('mongo');
    	
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
    
    function roomin($rid, $uid){
    	$room = $this->mongo->find('newborn.room');
    	p($room);
    	$this->mongo->insert('newborn.room', array($rid, $uid));
    }
}