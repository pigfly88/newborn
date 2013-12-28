<?php
class listModel extends Model{
	protected $table='pepsi_code';
	
	public function ss(){
		return $this->getOne(array('id'=>1));
	}
	
	
	
}