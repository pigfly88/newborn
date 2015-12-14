<?php

set_time_limit(0);
ini_set("memory_limit", "256M");
$pid = $_REQUEST['pid']; //可以启动多个进程

$pid_file = "{$pid}.pid"; //锁文件
$ctrl_file = "{$pid}.ctrl"; //控制文件

$idle_interval = 10000000; //空闲等待时间,微秒

//已有进程正在运行
if (file_exists($pid_file)) {
	//控制文件存在或者锁文件5分钟未被访问
	$stop = file_exists($ctrl_file);
	clearstatcache();
	if ($stop || ($_SERVER['REQUEST_TIME'] - fileatime($pid_file) >= 300)) {
		if ($pid = file_get_contents($pid_file)) {
			if ($stop) {
				@unlink($ctrl_file);
			}
			@unlink($pid_file);
			shell_exec("ps -ef | grep 'm=async&p=stat' | grep '{$pid}' | awk '{print \$2}' | xargs --no-run-if-empty kill"); //防止误杀
			$logStr = date('Ymd H:i:s') . ($stop ? " Killed by control file: $pid.ctrl" : " Process has gone away: $pid.pid");
			fc::debug($logStr, 'Async_Call');
		}
	}
}else { //第一次运行
	if (!file_put_contents($pid_file, getmypid())) {
		die("Can not create PID file: {$pid_file}");
	}
	$starttime = time();
	
	while (1) {
		if(time() - $starttime >= 3600){ //每一小时重启一下
			break;
		}
		
		touch($pid_file); //用于记录最后运行时间
		// some code
		
	}
}