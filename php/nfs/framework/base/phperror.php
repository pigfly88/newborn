<?php
!defined('BOYAA') AND exit('Access Denied!');
class phperror{
	public static function error_handler($errno, $errstr, $errfile, $errline){
		$error[] = date( 'Y-m-d H:i:s');
		$error[] = 'type:' . self::parse_errortype($errno);
		$error[] = 'msg:' . $errstr;
		$error[] = 'file:' . $errfile;
		$error[] = 'line:' . $errline;
		$error[] = 'uri:' . $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
		$error = implode('# ', $error);
		$trace = debug_backtrace();
		var_dump('error', $trace);
		foreach ( (array)$trace as $k => $v){
			if ( $k == 0 ) continue;
			$error .= 'file:'.$trace[$k]['file'].'; line:'.$trace[$k]['line'].'; function:'.$trace[$k]['function'].'; args:'.var_export((array)$trace[$k]['args'],true)."\n";
		}

		echo "<hr />";
	}
	
	public static function shutdown_function(){
		$lasterror = error_get_last();
		if(in_array($lasterror['type'], array(E_NOTICE, E_STRICT, E_DEPRECATED)))	return true;
		$now = date( 'Y-m-d H:i:s');
		$file = str_replace(ROOTPATH, '', $lasterror['file']);
		var_dump(CNAME, $file);exit;
		$errortype = self::parse_errortype($lasterror['type']);
		if(by::redis('backup')->sAdd('errormsg', "{$file}:{$lasterror['line']}")){//报警
			
			
		}else{//只累加错误次数
			
		}
		$error = "{$now} $errortype: {$lasterror['message']} in {$lasterror['file']} on line {$lasterror['line']}";
		echo $error;
	}
	
	public static function parse_errortype($type){
		switch($type){
			case E_ERROR: // 1 //
				return 'E_ERROR';
			case E_WARNING: // 2 //
				return 'E_WARNING';
			case E_PARSE: // 4 //
				return 'E_PARSE';
			case E_NOTICE: // 8 //
				return 'E_NOTICE';
			case E_CORE_ERROR: // 16 //
				return 'E_CORE_ERROR';
			case E_CORE_WARNING: // 32 //
				return 'E_CORE_WARNING';
			case E_COMPILE_ERROR: // 64 //
				return 'E_COMPILE_ERROR';
			case E_CORE_WARNING: // 128 //
				return 'E_COMPILE_WARNING';
			case E_USER_ERROR: // 256 //
				return 'E_USER_ERROR';
			case E_USER_WARNING: // 512 //
				return 'E_USER_WARNING';
			case E_USER_NOTICE: // 1024 //
				return 'E_USER_NOTICE';
			case E_STRICT: // 2048 //
				return 'E_STRICT';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'E_RECOVERABLE_ERROR';
			case E_DEPRECATED: // 8192 //
				return 'E_DEPRECATED';
			case E_USER_DEPRECATED: // 16384 //
				return 'E_USER_DEPRECATED';
		}
		return $type;
	}
	
}