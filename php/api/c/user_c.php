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
		//$list = oo::m('room')->get();
		//p($list);
		if(!$rid = $this->req('rid', 0, 'intval')){
			exit('rid error');
		}
		if(!$uid = $this->req('uid', 0, 'intval')){
			exit('uid error');
		}
		if($this->m->roomin($rid, $uid)){
			echo 'room in success';
		}
	}

	function roomout(){
	
	}
	
	
}