<?php
/**
* 计划任务-牌局解析
*/
class Cli_Resultlog
{
	//牌局解析-入口文件
	function start()
	{
		set_time_limit(0);
		error_reporting(E_ALL ^ E_NOTICE);

		while(1){
			$key = Resultlog::pop();
			if(!$key)
			{
				break;
			}
			
			$result_tmp = Resultlog::get($key);
			echo "\r\n## [" . $key . "] [" . $result_tmp . "]\r\n";
			
			if(empty($result_tmp))
			{
				fc::debug("key {$key} value empty [" . date('Y-m-d H:i:s') . "]", 'resultlog');		//写键取不到值的错误日志
				continue;
			}
			
			$result_detail = json_decode($result_tmp, TRUE);
			if(empty($result_detail))
			{
				echo "json decode error";
				fc::debug("{$result_tmp} json decode error [" . date('Y-m-d H:i:s') . "]", 'resultlog');	//写json解析错误日志
				continue;
			}
			
			Resultlog::doEvent($result_detail);
		}
	}
}