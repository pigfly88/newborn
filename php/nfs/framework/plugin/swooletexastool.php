<?php

/**
 * 主要用于处理TexasToolService服务
 *
 * @author JsonChen
 */
class ModelSwooleTexasTool {
	public $SwooleIp, $SwoolePort;

	public function __construct() {
		$this->SwooleIp =  "192.168.202.91";
		$this->SwoolePort = 38888;
	}

	/**
	 * 获取CrontabService进程内部监控信息
	 * @return type
	 */
	public function getCurrentMonitorInfo() {
		return TSwooleClient::GetCurrentMonitorInfo($this->SwooleIp, $this->SwoolePort);
	}
	/**
	 * 测试Swoole是否在工作
	 * @return int
	 */
	public function TestSwooleIsWorking(){
		return TSwooleClient::TestSwoole($this->SwooleIp, $this->SwoolePort);
	}
	/**
	 * 将数据发至TexasToolService
	 * @param type $data
	 */
	public function SendUdpToTexasTool($data,$type=1){	
		$data = json_encode($data);
		return TSwooleClient::SendByUdp($this->SwooleIp, $this->SwoolePort, $type, $data);
	}
	/**
	 * 重启指定的swoole进程
	 * @param type $type 1:只重启 task进程 2：重启 work及task进程
	 */
	public function ReloadSwooleByTcp() {
		return TSwooleClient::ReloadSwoole( $this->SwooleIp, $this->SwoolePort);
	}

}
