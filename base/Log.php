<?php
interface ILog{
	
	
	public function __construct(){
		$this->startTime = $this->microtime_float();
	}
	
	public function __destruct(){
		$this->endTime = $this->microtime_float();
		echo $this->endTime - $this->startTime;
	}
	
	public function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}