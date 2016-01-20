<?php
/**
 * 主要用于处理Texas使用的UDP协议服务
 *
 * @author JsonChen
 */
class ModelSwooleCrontab {
	public $SwooleTcpIp, $SwooleUdpIp, $SwooleTcpPort, $SwooleUdpPort;
	
	public function __construct() {	
		
		if (oo::$config['swoole_crontabip_port']) {
			$ipport = oo::$config['swoole_crontabip_port'];
		} else {
			if (!PRODUCTION_SERVER) {
				$ipport = '127.0.0.1:9501,127.0.0.1:9501';
			} else {
				$ipport = '127.0.0.1:59501,127.0.0.1:59501';
			}
		}
		$infos = explode(',', $ipport);
		list($tcpip, $tcpport) = explode(':', $infos[0]);
		$this->SwooleTcpIp = $tcpip;
		$this->SwooleTcpPort = $tcpport + oo::$config['sid'];
		if (isset($infos[1])) {
			list($udpip, $udpport) = explode(':', $infos[1]);
			$this->SwooleUdpIp = $udpip;
			$this->SwooleUdpPort = $udpport + oo::$config['sid'];
		} else {
			$this->SwooleUdpIp = $this->SwooleTcpIp;
			$this->SwooleUdpPort = $this->SwooleTcpPort;
		}
	}
	/**
	 * 获取CrontabService进程内部监控信息
	 * @return type
	 */
	public function getCurrentMonitorInfo(){
		return TSwooleClient::GetCurrentMonitorInfo($this->SwooleTcpIp, $this->SwooleTcpPort);
	}
	/**
	 * 获取异步任务执行记录
	 */
	public function GetCrontabLogList($local_ip) {
		$local_ip = ip2long($local_ip);
		$serverInfo = $this->getCrontabServerInfo();
		$ipList = array();
		$lastIp = '';
		foreach ($serverInfo as $ip=>$ret){
			$ipList[long2ip($ip)] = $ret['main'] ?'主web':'从web';
			if(!$local_ip && $ret['main']){
				$local_ip = $ip;
			}
			$lastIp=$ip;
		}
		$local_ip = $local_ip?$local_ip:$lastIp;
		$result['ip'] = long2ip($local_ip);
		$result['ipList'] = $ipList;
		
		
		
		$cacheKey = "SWOOLE_MONITOR_LOGLIST|" . $local_ip;
		$data = ocache::mongoTemp()->get($cacheKey);
		if(!$data){
			$data = array();
		}
		foreach ($data as &$log) {
			if($log['runcnt']){
				$log['avg_runtime'] = $log['runtime'] / $log['runcnt'] * 1000;
				$log['avg_runtime'] = ceil($log['avg_runtime']);
			}
			$log['begintime'] = date('Y-m-d H:i:s', $log['begintime']);
			$log['lasttime'] = date('Y-m-d H:i:s', $log['lasttime']);
			$log['maxtime_logtime'] = date('Y-m-d H:i:s', $log['maxtime_logtime']);
			$log['maxtime'] =intval($log['maxtime']);
		}
		$result['log'] = $data;
		return $result;
	}
	/**
	 * 获取 Swoole进程监控信息
	 * @return type
	 */
	public function getCrontabServerInfo(){		
		$cacheKey = "SWOOLE_MONITOR_SERVERLIST";
		$data =  ocache::mongoTemp()->get($cacheKey);
		if(!$data){
			$data = array();
		}
		return $data;
	}

	/**
	 * 测试Swoole是否在工作
	 * @return int
	 */
	public function TestSwooleIsWorking(){
		return TSwooleClient::TestSwoole('127.0.0.1', $this->SwooleTcpPort);
	}
	/**
	 * 重启指定的swoole进程
	 */
	public function ReloadSwooleByTcp() {
		return TSwooleClient::ReloadSwoole('127.0.0.1', $this->SwooleTcpPort);
	}
}
