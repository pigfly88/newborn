<?php
class indexModel extends Model{
	protected $table='tbl_user';
	
	public function getCode(){
		$res = $this->getOne(array('id'=>1));
		return $res;
	}
	
	
	
}