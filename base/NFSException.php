<?php
function customError($errno, $errstr, $errfile, $errline){
	echo "<b>Error:</b> [${errno}] ${errstr}<br />";
	echo "File:{$errfile} Line:{$errline}<br />";
}

class NFSException extends Exception {
	public function __construct($msg){
		if(C('debug.on')){
			echo "error: $msg <br />file: ".$this->getFile()."<br />code: ".$this->getCode();
			exit;
		}
	}
}
