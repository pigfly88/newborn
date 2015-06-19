<?php
class room_m extends model{
	protected $table = 'newborn.room';
	protected $mongo;
    public function _init(){
    	parent::_init();
    	$this->mongo = oo::cache('mongo');
    	
    }
    
    function get($rid=0){
    	if($rid){
    		$res = $this->mongo->findOne($this->table, array(0=>$rid));
    	}else{
    		$res = $this->mongo->find($this->table);
    	}
    	return $res;
    }
    
    function create($data){
    	$this->mongo->insert($this->table, $data);
    }
    
    
    
}