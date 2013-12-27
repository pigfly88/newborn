<?php
function customError($errno, $errstr, $errfile, $errline){
	echo "<b>Error:</b> [${errno}] ${errstr}<br />";
	echo "File:{$errfile} Line:{$errline}<br />";
}

class NFSException extends Exception {
	public function __construct($msg){
		if(C('debug.on')){
			echo "error: $msg <br />
			code: ".$this->getCode()."<br />
			file: ".$this->getFile()."<br />		
			line: ".$this->getLine();
			exit;
		}
	}
}
