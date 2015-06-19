<?php
/**
 * 牌局
 * @author BarryZhu
 *
 */
class card_c extends base_c {
	protected $cards = array(2,3,4,5,6,7,8,9,10,11,12,13,14);//2,3,4,5,6,7,8,9,10,J,Q,K,A
	protected $suit = array(1,2,3,4);//花色(s-spades黑桃 h-hearts红桃 d-diamonds方块 c-clubs梅花)
	
	public function _init(){
		
	}
	
	/**
	 * 发牌
	 * @param number $step 0-底牌	1-翻牌	2-转牌	3-河牌
	 */
	function deal($step=0){
		
	}
	
	/**
	 * 洗牌
	 */
	function shuffle(){
		
	}
}