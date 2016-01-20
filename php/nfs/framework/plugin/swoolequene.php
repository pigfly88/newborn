<?php

/**
 * 主要用于QueneService服务 交互通信接口
 *
 * @author JsonChen
 */
class ModelSwooleQuene {

	const Action_Log = 0x102;
	const Action_Callback = 0x101;
	const Action_Udp = 0x103;
	const Action_MTTSIGN = 0x104;

	public $SwooleTcpIp, $SwooleUdpIp, $SwooleTcpPort, $SwooleUdpPort;

	public function __construct() {
		$ipport = '';
		if (oo::$config['swoole_socketip_port']) {
			$ipport = oo::$config['swoole_socketip_port'];
		} else {
			if (!PRODUCTION_SERVER) {
				$ipport = '0.0.0.0:8501,0.0.0.0:8501';
			} else {
				$ipport = '0.0.0.0:59502,0.0.0.0:59502';
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
	 * 获取 QueneService 进程启动信息
	 * @return type
	 */
	public function getCrontabServerInfo() {
		$cacheKey = "SWOOLE_MONITOR_SERVERLIST_QUENE";
		$data = ocache::mongoTemp()->get($cacheKey);
		if (!$data) {
			$data = array();
		}
		return $data;
	}

	/**
	 * 获取QueneService进程内部信息
	 * @return type
	 */
	public function getCurrentMonitorInfo() {
		return TSwooleClient::GetCurrentMonitorInfo($this->SwooleTcpIp, $this->SwooleTcpPort);
	}
	/**
	 * 测试Swoole是否在工作
	 * @return int
	 */
	public function TestSwooleIsWorking() {
		return TSwooleClient::TestSwoole('127.0.0.1', $this->SwooleTcpPort);
	}

	/**
	 * 重启指定的swoole进程
	 * @param type $type 1:只重启 task进程 2：重启 work及task进程
	 */
	public function ReloadSwooleByTcp() {
		return TSwooleClient::ReloadSwoole('127.0.0.1', $this->SwooleTcpPort);
	}

	/**
	 * Udp发送Callback信息
	 * @param type $contentData
	 */
	public function SendToCallBack($contentData) {
		TSwooleClient::SendByUdp("127.0.0.1", $this->SwooleUdpPort, self::Action_Callback, $contentData);
	}

	/**
	 * Udp发送mtt信息
	 * @param type $contentData
	 */
	public function SendToMttInfo($contentData) {
		TSwooleClient::SendByUdp("127.0.0.1", $this->SwooleUdpPort, self::Action_MTTSIGN, $contentData);
	}

	/**
	 * 原Udp业务处理
	 * @param type $contentData
	 */
	public function SendToUdp($contentData) {
		TSwooleClient::SendByUdp("127.0.0.1", $this->SwooleUdpPort, self::Action_Udp, $contentData);
	}

}
