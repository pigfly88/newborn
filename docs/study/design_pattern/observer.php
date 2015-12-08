<?php
header("Content-type: text/html; charset=utf-8");

//前台mm
class qtmm{
	protected $cxys;
	
	public function fangshao($cxy){
		$this->cxys[] = $cxy;
		echo "mm: 我帮{$cxy->name}放哨，老板来了就通知他...<br />";
	}
	
	public function notice(){
		foreach($this->cxys as $v){
			echo "mm: 通知{$v->name}老板来了...<br />";
			$v->work();
		}
	}
}

//程序猿
class cxy{
	public $name;
	public function __construct($name){
		$this->name = $name;
	}
	public function play(){
		echo "{$this->name}: 玩下游戏^^...<br />";
	}
	
	public function work(){
		echo "{$this->name}: 切换成工作模式...<br />";
	}
}

//老板走了，程序猿开始玩游戏了
$cxy1 = new cxy('a');
$cxy2 = new cxy('b');
$cxy1->play();
$cxy2->play();

//前台mm再前台守着，答应帮程序猿放哨
$mm = new qtmm();
$mm->fangshao($cxy1);
$mm->fangshao($cxy2);

//老板来了，前台mm赶紧通知程序猿去工作，别玩游戏了
$mm->notice();


class cd{
	public $title='';
	public $band='';
	protected $_observers = array();
	
	public function __construct($title, $band){
		$this->title = $title;
		$this->band = $band;
	}
	
	public function attach_observer($type, $observer){
		$this->_observers[$type][] = $observer;
	}
	
	public function notify_observer($type){
		foreach($this->_observers[$type] as $v){
			$v->update($this);
		}
	}
	
	public function buy(){
		$this->notify_observer('buy');
	}
}