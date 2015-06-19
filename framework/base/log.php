<?php
/*class log{
	
	
	function start(){
		$this->startTime = $this->microtime_float();
	}
	
	public function end(){
		$this->endTime = $this->microtime_float();
		echo $this->endTime - $this->startTime;
	}
	
	public function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
*/
	function shutdown($params){
		log_error($params['log_error']);
	}
	function log_error($param){
		$error = error_get_last();
		function_exists('fastcgi_finish_request') && fastcgi_finish_request();
		if ( !is_array( $error) || !in_array($error['type'], array(E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR)) ) return;
	
		$error = '';
		$error .= date('Y-m-d H:i:s') . '--';
		//$error .= 'Type:' . $error['type'] . '--';
		$error .= 'Msg:' . $error['message'] . '--';
		$error .= 'File:' . $error['file'] . '--';
		$error .= 'Line:' . $error['line'] . '--';
		$error .= 'Ip:' . (isset( $_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0') . '--';
		$error .= 'Uri:' . (isset( $_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ''). '--';
		$error .= empty( $_SERVER['CONTENT_TYPE'] ) ? '' : 'Content-Type:' . $_SERVER['CONTENT_TYPE'].'--';
		$error .= $_SERVER['REQUEST_METHOD'].$_SERVER['REQUEST_URI'];
		$error .= '<br/>';
		$size = file_exists( $file) ? @filesize( $file) : 0;

		file_put_contents(APP_ROOT.APP_DIR.'/webroot/data/logs/'.$param['file'], $error, $size<$param['maxsize'] ? FILE_APPEND : null);
	}
	
	function nfs_error($errno, $errstr, $errfile, $errline){ //捕获错误
		$error = '';
		$error .= date( 'Y-m-d H:i:s') . '--';
		$error .= 'Type:' . $errno . '--';
		$error .= 'Msg:' . $errstr . '--';
		$error .= 'File:' . $errfile . '--';
		$error .= 'Line:' . $errline . '--';
		$error .= 'Ip:' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0') . '--';
		$error .= 'Uri:' . (isset( $_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : ( isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '' ) ) . "\n";
		
		$error .= '<br />';
		$aTrc = debug_backtrace();
		foreach ( (array)$aTrc as $k => $v){
			if ( $k == 0 ) continue;
			if( ( $aTrc[$k]['function'] == "mysql_connect") && ( isset($aTrc[$k]['args'][2]) ) )  unset($aTrc[$k]['args'][2]);
			$error .= 'file:'.$aTrc[$k]['file'].'; line:'.$aTrc[$k]['line'].'; function:'.$aTrc[$k]['function'].'; args:'.var_export((array)$aTrc[$k]['args'],true)."\n";
		}
	
		$file = APP_ROOT.APP_DIR.'/webroot/data/logs/nfserror.php';
		$size = file_exists( $file) ? @filesize( $file) : 0;
		$flag = $size < 1024*1024; //标志是否附加文件.文件控制在1M大小
		/*$prefix = $size && $flag ? '' : "<?php (isset(\$_GET['p']) && (md5('&%$#'.\$_GET['p'].'**^')==='8b1b0c76f5190f98b1110e8fc4902bfa')) or die();?>\n";
		*/
		$prefix = $size && $flag ? '' : "<?php \$_GET['p']!='asd' && die();?>\n";
		file_put_contents($file, $prefix.$error, $flag ? FILE_APPEND : null);
	}

	
//}
set_error_handler( 'nfs_error', defined('E_DEPRECATED') ? E_ALL ^ E_NOTICE ^ E_DEPRECATED : E_ALL ^ E_NOTICE); //注册错误函数
register_shutdown_function( 'shutdown', array('log_error'=>array('maxsize'=>10240*1024, 'file'=>'phperror.php')));