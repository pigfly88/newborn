<?php
class room_m extends model{
	protected $table = 'newborn.room';
	protected $mongo;
    public function _init(){
    	parent::_init();
    	$this->mongo = oo::cache('mongo');
    	
    }
    
    function get($rid){
    	$this->mongo->find($this->table, array(rid));
    }
    
    function create($data){
    	$this->mongo->insert($this->table, $data);
    }
}