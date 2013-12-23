<?php
class listModel extends Model{
	protected $table='user';
	
	public function ss(){
		return $this->getOne(array('id'=>1));
	}
	
	
	
}