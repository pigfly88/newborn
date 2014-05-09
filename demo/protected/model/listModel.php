<?php
class listModel extends Model{
	protected $table='pepsi_code';
	
	public function getCode(){
		$cache = Cache::init('memcache')->get('code');
		if($cache) return $cache;
		
		$res = $this->getColumn(array('id'=>4), 'code');
		Cache::init('memcache')->set('code', $res);
		return $res;
	}
	
	
	
}