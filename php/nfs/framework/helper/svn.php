<?php
/**
 * 异步更新SVN
 */
set_time_limit(0 ); //不超时
//$runver = '2012121309'; //运行版本
$host = '0.0.0.0'; //监听ip
$port = 55555; //监听端口
$runFile = "/tmp/svnserver.run";
$processFile = "/tmp/svnserver_" . $host . '_' .  $port; //进程控制文件
//$runverFile = "/tmp/svnserverrunver_" . $host . '_' .  $port; //运行文件版本
$errorFile = "/tmp/svnservererror_" . $host . '_' . $port . '.txt'; //错误文件
$logFile = "/tmp/svnserverlog_" . $host . '_' .  $port . '.txt'; //日志文件

if( file_exists( $runFile)){ 
	$subtime = time()-filemtime($runFile);
	if ( ( $subtime > 3600)) { //锁文件存在但最后更新时间超过了一小时未更新
		unlink( $runFile);//删除锁文件
		@file_put_contents( $errorFile, date('Y-m-d H:i:s') . " : $runFile is timeout ! " . $subtime . "\n", FILE_APPEND);
		`ps -ef|awk '/svnserver/ && !/awk/{print $2}'|xargs --no-run-if-empty kill`; //匹配出并杀掉防止假死的进程
	}else {
		@file_put_contents( $processFile, date('Y-m-d H:i:s') . "\n", FILE_APPEND);
		die();
	}
}

if( ($sSocket = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP)) === false){
	@file_put_contents( $errorFile, date('Y-m-d H:i:s') . " : " . socket_strerror(socket_last_error()) . "\n", FILE_APPEND);
	die('Create Socket Error!');
}

if( socket_bind($sSocket, $host, $port) === false){
	@file_put_contents( $errorFile, date('Y-m-d H:i:s') . " : " . socket_strerror(socket_last_error()) . "\n", FILE_APPEND);
	die('Bind Socket Error!');
}

if(! file_exists( $runFile)){
	@file_put_contents( $errorFile, date('Y-m-d H:i:s') . ": $runFile is not found ! " . $errmsg . "\n", FILE_APPEND);
	while ( true) {
		touch( $runFile);
		if( @socket_recvfrom($sSocket, $content, 4096, 0, $ip, $port) === false){//取消息
			@file_put_contents( $errorFile, date('Y-m-d H:i:s') . " : " . socket_strerror(socket_last_error()) . "\n", FILE_APPEND);
			continue;
		}
		if(!$content){
			continue;
		}
		$cmd = trim(unserialize($content));
		@file_put_contents( $logFile, date('Y-m-d H:i:s') . $cmd . $content ."\n" , FILE_APPEND);
		if(stripos($cmd, 'zh_cn/demo/') !== 0){
			continue;
		}
		$aSvnDir = array();
		$aSvnDir[] = "/data/wwwroot/" .  str_ireplace('zh_cn/demo/', '', $cmd);
		try{
			foreach ( $aSvnDir as $dirname){
				if ( ! is_dir($dirname)) {
					continue;
				}
				@file_put_contents( $logFile, date('Y-m-d H:i:s') . "\n" . `/usr/bin/svn cleanup {$dirname} --no-auth-cache --non-interactive --username SvnUser --password 'bys1v2n3&&'` . "\nclear {$dirname} ok!\n", FILE_APPEND);
				@file_put_contents( $logFile, date('Y-m-d H:i:s') . "\n" . `/usr/bin/svn update {$dirname} --no-auth-cache --non-interactive --username SvnUser --password 'bys1v2n3&&'` . "\nupdate {$dirname} ok!\n", FILE_APPEND);
			}
		}catch(Exception $e ){
		}
	}
}
@socket_close( $sSocket);
die();
