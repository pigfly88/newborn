<?php

/**
 * 异步执行脚本
 */
class Cli_Async {
	//* * * * *
	function stat() {
		set_time_limit(0);
		ini_set("memory_limit", "256M");
		$pflag = $_REQUEST['pflag'];
		$g = $_REQUEST['g'];
		$pid_file = ROOTPATH . "data/async/AC-{$g}-{$pflag}.pid"; //锁文件
		$ctrl_file = ROOTPATH . "data/async/AC-{$g}-{$pflag}.ctrl"; //控制文件
		$exec_interval = 50000; //执行间隔时间,微秒
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
					$logStr = date('Ymd H:i:s') . ($stop ? " Killed by control file: $pflag.ctrl" : " Process has gone away: $pflag.pid");
					fc::debug($logStr, 'Async_Call');
				}
			}
		} //第一次运行
		else {
			if(!is_dir(ROOTPATH . "data/async")) mkdir(ROOTPATH . "data/async", 0775, true);
			$flag = @file_put_contents($pid_file, getmypid());
			if (!$flag) {
				fc::debug("Can not create PID file: {$pid_file}", 'Async_Call');
				exit;
			}
			while (1) {
				touch($pid_file); //用于记录最后运行时间
				Async::combineInsert(1);//DB的操作
				$msg = Async::output_new();
				if (empty($msg) || !is_array($msg)) {
					usleep($idle_interval);
					continue;
				}
				$func = array_shift($msg);
				if (is_callable($func)) {
					//Class::method
					call_user_func_array($func, $msg);
				} else if (strpos($func, '->') !== false) {
					//Object->method
					$obj_fun_arr = explode('->', $func);
					if (count($obj_fun_arr) > 1) {
						eval('$obj=' . $obj_fun_arr[0] . ';');
						if (is_object($obj)) {
							if (is_callable(array($obj, $obj_fun_arr[1]))) {
								call_user_func_array(array($obj, $obj_fun_arr[1]), $msg);
							} else {
								fc::debug($func . '--' . json_encode($msg) . ' no is_callable ' . date('Ymd H:i:s'), 'Async_problem');
							}
						} else {
							fc::debug($func . '--' . json_encode($msg) . ' no is_object ' . date('Ymd H:i:s'), 'Async_problem');
						}
					} else {
						fc::debug($func . '--' . json_encode($msg) . ' no count ' . date('Ymd H:i:s'), 'Async_problem');
					}
				} else {
					fc::debug($func . '--' . json_encode($msg) . ' has problem ' . date('Ymd H:i:s'), 'Async_problem');
				}
				//if($count++ >= 10000) break; //每执行10000行重启一下
			}
		}
	}
}
