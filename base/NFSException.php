<?php
function customError($errno, $errstr, $errfile, $errline){
	echo "<b>Error:</b> [${errno}] ${errstr}<br />";
	echo "File:{$errfile} Line:{$errline}<br />";
}

class NFSException extends Exception {
	public function __construct($msg){
		/*
		if(C('debug.on')){
			echo "error: $msg <br />
			code: ".$this->getCode()."<br />
			file: ".$this->getFile()."<br />		
			line: ".$this->getLine();
			exit;
		}
		*/
	}
    
    public function log(){
        foreach((array)$this->getTrace() as $v){
            $args = var_export($this['args'], true);
            $log .= "[{$this->getFile()};{$this->getLine()};{$this->getFunction()};{$args};] \n";
        }
        $error = $this->getCode() . $this->getMessage() . ' '.  $log . date("H:i:s") . '[]';
        echo $error;
    }
    
    
    
    
    
}
