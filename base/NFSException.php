<?php
class NFSException extends Exception {
	function __construct($msg){
		echo $msg;exit;
	}
}
