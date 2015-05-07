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
    	if(!$room = $this->mongo->find('newborn.room', array('rid'=>$rid))){
    		return false;
    	}

    	if($this->mongo->find('newborn.room_user', array('rid'=>$rid, 'uid'=>$uid))){//已经在房间里了
    		return true;
    	}
    	
    	if($this->mongo->insert('newborn.room_user', array('rid'=>$rid, 'uid'=>$uid))){//进房间成功
    		$room_user = $this->mongo->find('newborn.room_user', array('rid'=>$rid));//拉取房间里的玩家
    		//通知房间里的玩家 有客到
    		foreach ($room_user as $v){
    			if($v['uid']!=$uid){
    				echo "notice {$v['uid']}, {$uid} in<br />";
    			}
    		}
    		return true;
    	}
    	return false;
    }
    
    function sitdown(){
    	return $this->mongo->update('newborn.room_user', array($rid, $uid), array('status'=>1));
    }
    
    
    
    
    
}