<?php
!defined('BOYAA') AND exit('Access Denied!');
/**
 * php错误通知
 * @author BarryZhu 20150724
 *
 */
class phperror{
	
	public static function error_handler($errno, $errstr, $errfile, $errline){
		if(in_array($errno, array(E_NOTICE, E_STRICT, E_DEPRECATED)))	return true;
		if(false!==strstr($errstr, 'mysql_connect') || false!==strstr($errstr, 'mysql_pconnect'))	return true;
		
		$errtype = self::parse_errortype($errno);
		$errfile = str_replace(ROOTPATH, '', $errfile);
		$msg = "$errtype: {$errstr} in file:/{$errfile} on line {$errline}";
		fc::debug($msg, 'phperr.txt');
	}

	public static function shutdown_function(){
		$lasterror = error_get_last();//shutdown只能抓到最后的错误，trace无法获取
		if(in_array($lasterror['type'], array(E_NOTICE, E_STRICT, E_DEPRECATED, E_USER_NOTICE, E_USER_DEPRECATED)))	return true;

		$file = str_replace(ROOTPATH, '', $lasterror['file']);
		$errtype = self::parse_errortype($lasterror['type']);
		$msg = "$errtype: {$lasterror['message']} in file:/{$file} on line {$lasterror['line']}";
		fc::debug($msg, 'phperr.txt');
	}
	
	private static function parse_errortype($type){
		switch($type){
			case E_ERROR: // 1 //
				return 'Fatal Error';
			case E_WARNING: // 2 //
				return 'Warning';
			case E_PARSE: // 4 //
				return 'Parse error';
			case E_NOTICE: // 8 //
				return 'Notice';
			case E_CORE_ERROR: // 16 //
				return 'Core error';
			case E_CORE_WARNING: // 32 //
				return 'Core warning';
			case E_COMPILE_ERROR: // 64 //
				return 'Compile error';
			case E_COMPILE_WARNING: // 128 //
				return 'Compile warning';
			case E_USER_ERROR: // 256 //
				return 'User error';
			case E_USER_WARNING: // 512 //
				return 'User warning';
			case E_USER_NOTICE: // 1024 //
				return 'User notice';
			case E_STRICT: // 2048 //
				return 'Strict Notice';
			case E_RECOVERABLE_ERROR: // 4096 //
				return 'Recoverable Error';
			case E_DEPRECATED: // 8192 //
				return 'Deprecated';
			case E_USER_DEPRECATED: // 16384 //
				return 'User deprecated';
		}
		return $type;
	}
	
}