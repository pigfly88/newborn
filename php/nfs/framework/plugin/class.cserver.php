<?php
/*
  php 与 server 进行交互的类
*/
class Cserver {
	
	const CMD_UPDATE_COIN = 0x1100; //更新用户金币

	const CLIENT_COMMAND_GET_RECORD = 0x1105; //取用户钱数,经验值
	

	private $aSockets = array (); //暂存各Socket连接.防止一个脚本多次连同一个Server. [$ip][$port] = socket;
	

	private $MemDataServer = array ();
	
	public static $_instance = array ();
	
	/**
	 * 创建一个实例
	 * @return object Cserver
	 */
	public static function factory() {
		if (! is_object ( self::$_instance ['cserver_api'] )) {
			self::$_instance ['cserver'] = new self ();
		}
		
		return self::$_instance ['cserver'];
	}
	
	//实例化：主要是设置 server端的 ip地址 和 端口号。之后要提出去
	public function __construct() {}
	
	/**
	 * 
	 * 
	 * 
	 * 更新用户金币：增加和减去
	 * @uInfoArrays    需要修改的用户信息集合。
	 * 例如
	 * array(
	 * "user1"=>array('mid'=>1,'api'=>8988,'money'=>123,'act_id'=>"17"),
	 * "user2"=>array('mid'=>1,'api'=>8988,'money'=>123,'act_id'=>"17"),
	 * )
	 */
	public function updateCoin($uInfoArrays) {
		if (empty ( $uInfoArrays )) {
			return 0;
		}
		$count = count ( $uInfoArrays );
		//构建消息包实例
		$packet = by::socketpacket();
		
		//############################第一步设定一些消息包的初始值  注意此步中还未填写包头只是 做了一些初始值，一遍在完成包组装的过程中 填写包头
		$packet->WriteBegin ( self::CMD_UPDATE_COIN );
		
		//############################第二步写包体
		//1.向包中写明 麻将类型
		$packet->WriteInt_N ( SERVERGAMEID );
		
		//2.向包中写明 需要更新的用户个数
		$packet->WriteInt_N ( $count );
		
		$value = $uInfoArrays[0];
		$mid = $value['mid'];
		
		//3.向包中循环写入 多个玩家的信息
		//echo $value ['mid'] . "---" . $value ['api'] . "---" . $value ['money'] . "---" . $value ['act_id'] . "<br/>";
		$packet->WriteInt_N ( $mid ); //用户mid
		$packet->WriteInt_N ( $value ['api'] ); //用户api(C++方叫做 form)
		$packet->WriteInt_N ( $value ['money'] ); //需要加或者减的金币数额 (正数表示加，负数表示减)
		$packet->WriteString_N ( $value ['act_id'] ); //操作的act_id 例如 '17'表示 注册加金币
		
		//#############################第三步.完成包的组装
		$packet->WriteEnd_n ( "需要更新的用户个数" );
		
		//#############################第四步.发送数据包
		
		$this->initMServerByMid($mid);

		if (! $this->SendData ( $this->MemDataServer [0], $this->MemDataServer [1], $packet, true )) {
			return 0;
		}
		
		userserver::get_instance()->flush_money($mid);	//通知userserver更新金币
		
		//#############################第四部.解析返回信息

		return 1; //返回的结果  1 成功 0 失败
	}
	
	/**********************以下为私有方法***********************************/
	/**
	 * 连接Tcp服务器
	 * @param String $ip
	 * @param int $port
	 * @param Boolean $reuse 是否使用上一次创建好的连接.用在一个脚本中与同一个端口多次通讯.只有存钱Server支持,其他都要强制重新连接
	 * @return socket/false
	 */
	private function connect($ip, $port, $reuse = false) {
		if (isset($this->aSockets[$ip][$port]) && is_resource($this->aSockets[$ip][$port]) && $reuse) { //已经连接并且支持...
			return $this->aSockets [$ip] [$port];
		}
		
		if(($socket = @socket_create ( AF_INET, SOCK_STREAM, SOL_TCP )) === false)
		{
			$errorcode = socket_last_error();
			Logger::commonLog("[{$ip}:{$port}] [{$errorcode}] " . socket_strerror($errorcode) , 'create_fail', 'socket' );
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_create_err');
			return 0;
		}
		
		$start_time = fc::microtime_float();
		if (@socket_connect ( $socket, $ip, $port ) === false) {
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			Logger::commonLog("[{$ip}:{$port}] [{$errorcode}] " . socket_strerror($errorcode) . "|start:{$start_time}|stop:{$end_time}|use:" . ($end_time - $start_time), 'connect_fail', 'socket' );
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_connect_err');
			return 0;
		}
		
		@socket_set_option ( $socket, SOL_SOCKET, SO_RCVTIMEO, array ("sec" => 1, "usec" => 0 ) );
		@socket_set_option ( $socket, SOL_SOCKET, SO_SNDTIMEO, array ("sec" => 1, "usec" => 0 ) );
		
		socket_set_block ( $socket );
		
		return $this->aSockets [$ip] [$port] = $socket;
	}
	
	/**
	 * 实际做的发送操作
	 * @param String $ip
	 * @param int $port
	 * @param GameSocketPacket $packet
	 * @return Boolean
	 */
	private function SendData($ip, $port, &$packet, $reuse = false) {
		if (! $this->connect ( $ip, $port, $reuse ))
		{
			return false;
		}
		
		if (@socket_write ( $this->aSockets [$ip] [$port], $packet->GetPacketBuffer (), $packet->GetPacketSize () ) === false) {
			$errorcode = socket_last_error();
			$errorstr = socket_strerror($errorcode);
			Logger::commonLog("[{$ip}:{$port}] [{$errorcode}] {$errorstr}", 'socket_write_error', 'socket' );
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_write_err');
			fc::debug("设置金币失败, #{$errorcode} {$errorstr} on {$ip}:{$port}", 'money');
			return false;
		}
		
		return true;
	}
	
	private function initMServerByMid($mid)
	{
		$money_servers = fc::getConfig('money_servers', 'inc');
		
		$modid = $mid % count($money_servers);
		
		if(!isset($money_servers[$modid]))
		{
			$modid = 0;
		}
		
		$this->MemDataServer = $money_servers[$modid];

		return true;
	}
}
