<?php
/**
 * 玩家
 * @author BarryZhu
 *
 */
class user_c extends base_c {
	
	public function _init(){
		
	}

	function roomin(){
		if(!$rid = $this->req('rid', 0, 'intval')){
			exit('rid error');
		}
		$uid = rand(1,1000);
		echo 'uid:'.$uid;
		$this->m->roomin($rid, $uid);
	}

	function roomout(){
	
	}
	
	
}