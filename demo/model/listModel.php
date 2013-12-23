<?php
class listModel extends Model{
	public function comment(){
		$list = DB::fetch('SELECT * FROM `test`.`tbl_lookup`');
		return $list;
	}
	
	
	
}