<?php
/**
 * 
 */
class Agentserver extends Server
{
	/*请求的MemberServer*/
	const MONEY_SERVER = 'money_servers';
	/*更新用户金币*/
	const CMD_UPDATE_COIN = 0x1100;
	/*取得用户信息userinfo表（金币，经验等)*/
	const CLIENT_COMMAND_GET_RECORD = 0x1105;
	/*更新用户信息（除金币外）*/
	const CLIENT_COMMAND_SET_RECORD = 0x1103;

	/*请求的UserServer*/
	const USER_SERVER = 'user_servers';
	/*强制下线*/
	const PHP_CLICK_USER = 0X6901;
	/*推送消息到客户端*/
	const PHP_PUST_MSG = 0X6900;


	/*红包server*/
	const FHB_SERVER = 'fhb_servers';
	/*推送红包数据给server*/
	const SERVER_PUSH_REDENVELOPE = 0x6506;	

	/*请求的HttpServer 客户端请求PHP由SERVER代理*/
	const HTTP_SERVER = 'http_servers';

	/*读取回包的buff长度*/
	const PACKET_RECV_BUFF_LEN = 8192;

	/*敏感词Server*/
	const DWF_SERVER = 'dwf_servers';
	/*敏感词过滤命令字*/
	const GET_GOOD_WORDS_TRANS = 0x13;

	/*请求server的ip*/
	private $_ip;
	/*请求Server的端口*/
	private $_port;
	/*PHP与SERVER麻将类型映射*/
	private $_mjCode = array(
		5  => 6,				// 四川麻将
		10 => 11,				// 二人麻将
		11 => 23				// 麻将合集
	);

	/*Server后台控制*/
    const SYSCMD_BACKDOOR_UNENCRY=0x520;
    const SYSCMD_BACKDOOR_ENCRY=0x521;

    /**
     * 模拟客户端走SERVER代理请求PHP
     * @var 	$postData 	string 	请求参数(josn)
     * @var 	$ip 		string 	请求Server的IP
     * @var 	$port 		intval  请求Server的端口
     * @var 	$return     array   Server返回结构
     */
    public function http_proxy($postData, $isZip=1, $ip='', $port=0)
    {
    	$isZip = intval($isZip) && intval($isZip) == 1 ? 1 : 0;
    	// 取得IP和端口配置
    	if (!empty($ip) && !empty($port))
    	{
    		$this->_ip = $ip;
    		$this->_port = $port;
    	}
    	else
    	{
			$this->_initServersConf(self::HTTP_SERVER);
    	}
    	$postData = trim($postData);
    	$postArr = !empty($postData) ? json_decode($postData, true) : array();
    	if (empty($postArr) || !is_array($postArr))
    	{
    		return false;
    	}
    	$endPostData = $isZip ? gzcompress($postData) : $postData;
    	$phpcmd = 0x1001;
        $intr = new SocketPacket();
		$intr->WriteBegin(0x0038);
		$intr->WriteInt_N($phpcmd);
		$intr->WriteInt_N($isZip);
		$isZip ? $intr->WriteBinary($endPostData, strlen($endPostData)) : $intr->WriteString_N($endPostData);
		$intr->WriteEnd_n('encry');

        $hallid=1;
        $sessionType=0;
        $session=0;
        $remote_ip = Helper::getIp();

		$packet = new SocketPacket();
        $packet->WriteBegin(0x0038);
        $packet->WriteInt_N($hallid);
        $packet->WriteInt_N($sessionType);
        $packet->WriteInt_N($session);
        $packet->WriteString_N($remote_ip);
        $packet->WritePacket($intr);
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			$errorcode = socket_last_error();
			$errorstr = socket_strerror($errorcode);
			fc::debug("http代理发送失败 {$this->_ip}:{$this->_port} #{$errorcode} - $errorstr", "server");
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, self::PACKET_RECV_BUFF_LEN, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$errorstr = socket_strerror($errorcode);
			fc::debug("http代理接收失败 #{$errorcode} - $errorstr", "server");
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret['hallid'] = $packet->ReadInt_N();
		$ret['sessionType'] = $packet->ReadInt_N();
		$ret['session'] = $packet->ReadInt_N();
		$ret['remote_ip'] = $packet->ReadString();

        $packet->ReadPacket($intr);
        $ret['phpcmd']=$intr->ReadInt_N();
        $ret['zip']=$intr->ReadInt_N();
        $compress = $ret['zip'] ? $intr->ReadBinary() : $intr->ReadString();
        fc::debug("compress:".$compress, "http_proxy");
        $ret['response'] = $ret['zip'] == 1 ? gzuncompress($compress) : $compress;

        $ret['host'] = $this->_ip;
        $ret['port'] = $this->_port;
		return $ret;
    }

	/**
	 * 取得用户信息
	 * @var 	$mid 	int 	用户id
	 * @return  $arr 	array 	用户信息
	 */
	public function mem_getinfo($mid)
	{
		$mid = Helper::uint($mid);
		if (empty($mid))
		{
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::MONEY_SERVER, $mid);
		// 构建消息包实例
		$packet = new SocketPacket();
		// 写命令字
		$packet->WriteBegin(self::CLIENT_COMMAND_GET_RECORD);
		// 写包体
		$packet->WriteInt_N($mid);
		// 结束包体
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret = $packet->ReadInt_N();
		if ($ret != 0)
		{
			return false;
		}
		$arr['mid'] 		= $packet->ReadInt_N();
		$arr['money'] 		= $packet->ReadInt_N();
		$arr['exp'] 		= $packet->ReadInt_N();
		$arr['level'] 		= $packet->ReadInt_N();
		$arr['wintimes'] 	= $packet->ReadInt_N();
		$arr['losetimes'] 	= $packet->ReadInt_N();
		$arr['drawtimes'] 	= $packet->ReadInt_N();
		return $arr;
	}

	/**
	 * 更新用户金币
	 * @var 	$mid 	int 	用户id
	 * @var 	$api 	int 	用户api(SERVER的from)
	 * @var 	$money 	int 	变化的金币数
	 * @var 	$act_id int 	操作标识
	 * @return  $arr 	array 	用户更新后的信息
	 */
	public function mem_updatecoin($mid, $api, $money, $act_id)
	{
		$mid = Helper::uint($mid);
		$api = Helper::uint($api);
		$money = intval($money);
		$act_id = Helper::uint($act_id);
		$serverGameId = $this->_getServerGameId();
		if (empty($mid) || empty($act_id) || empty($money) || empty($serverGameId))
		{
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::MONEY_SERVER, $mid);
		// 构建消息包实例
		$packet = by::socketpacket();
		// 写命令字
		$packet->WriteBegin(self::CMD_UPDATE_COIN);
		// 标识麻将类型
		$packet->WriteInt_N($serverGameId);
		// 更新的用户个数
		$packet->WriteInt_N(1);
		// 用户mid
		$packet->WriteInt_N($mid);
		// 用户api(server定义为from)
		$packet->WriteInt_N($api);
		// 需要加或者减的金币数额 (正数表示加，负数表示减)
		$packet->WriteInt_N($money);
		// 操作的act_id 例如 '17'表示 注册加金币
		$packet->WriteString_N($act_id);
		// 结束包体
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$errorstr = socket_strerror($errorcode);
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			fc::debug("更新金币失败, #{$errorcode} {$errorstr} on {$this->_ip}:{$this->_port}, mid:{$mid}, money:{$money}", 'money');
			return false;
		}
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret = $packet->ReadInt_N();

		if ($ret != 0)
		{
			fc::debug("更新金币失败, ret err on {$this->_ip}:{$this->_port}, mid:{$mid}, money:{$money}", 'money');
			return false;
		}
		if (GAMEID == 5)
		{
			return true;
		}
		$count = $packet->ReadInt_N();
		$arr['mid'] 		= $packet->ReadInt_N();
		$arr['money'] 		= $packet->ReadInt_N();
		$arr['exp'] 		= $packet->ReadInt_N();
		$arr['level'] 		= $packet->ReadInt_N();
		$arr['wintimes'] 	= $packet->ReadInt_N();
		$arr['losetimes'] 	= $packet->ReadInt_N();
		$arr['drawtimes'] 	= $packet->ReadInt_N();
		return $arr;
	}

	/**
	 * 更新用户信息(支持字段"exp","level","wintimes","losetimes","drawtimes")
	 * @example array('mid'=>15,'exp'=>100,'level'=>1,'wintimes'=>10,'losetimes'=>10,'drawtimes'=>10)
	 * @var 	$data 	array 	要更新的用户信息
	 * @var 	$act 	int 	更新方式　1:覆盖　2:相加
	 * @return  $arr 	array 	用户更新后的信息
	 */
	// act 1:覆盖 2:相加
	public function mem_updateinfo($data, $act=2)
	{
		if (empty($data) || !is_array($data) || !in_array($act, array(1, 2)))
		{
			return false;
		}
		$mid = isset($data['mid']) ? Helper::uint($data['mid']) : 0;
		if (empty($mid))
		{
			return false;
		}
		$attrArr = array();
		$fieldArr = array("exp"=>2,"level"=>3,"wintimes"=>4,"losetimes"=>5,"drawtimes"=>6);
		$fieldKey = array_keys($fieldArr);
		foreach ($data as $key => $val)
		{
			if (in_array($key, $fieldKey))
			{
				$attrArr[$key]['op'] = intval($act);
				$attrArr[$key]['type'] = intval($fieldArr[$key]);
				$attrArr[$key]['value'] = intval($val);
			}
		}
		if (empty($attrArr))
		{
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::MONEY_SERVER, $mid);
		// 构建消息包实例
		$packet = by::socketpacket();
		// 写命令字
		$packet->WriteBegin (self::CLIENT_COMMAND_SET_RECORD);
		// 写更新用户数量，暂时不支持批量（Server能支持,不过得按取模后传用户信息）
		$packet->WriteInt_N(1);
		// 写入MID
		$packet->WriteInt_N($mid);
		// 更新属性个数
		$packet->WriteByte(count($attrArr));
		// 循环写入要更新的属性
		foreach ($attrArr as $key => $val)
		{
			$packet->WriteInt_N($val['op']);
			$packet->WriteInt_N($val['type']);
			$packet->WriteInt_N($val['value']);
		}
		// 结束包体
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret = $packet->ReadInt_N();
		if ($ret != 0)
		{
			return false;
		}
		if (GAMEID == 5)
		{
			return true;
		}
		$count = $packet->ReadInt_N();
		$arr['mid'] 		= $packet->ReadInt_N();
		$arr['money'] 		= $packet->ReadInt_N();
		$arr['exp'] 		= $packet->ReadInt_N();
		$arr['level'] 		= $packet->ReadInt_N();
		$arr['wintimes'] 	= $packet->ReadInt_N();
		$arr['losetimes'] 	= $packet->ReadInt_N();
		$arr['drawtimes'] 	= $packet->ReadInt_N();
		return $arr;
	}

	/**
	 * 强制用户下线
	 * @var 	$mid 	int 	用户id
	 * @return  
	 */
	public function mem_offline($mid)
	{
		$mid = Helper::uint($mid);
		if (empty($mid))
		{
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::USER_SERVER, $mid);
		// 构建消息包实例
		$packet = new SocketPacket();
		// 写命令字
		$packet->WriteBegin(self::PHP_CLICK_USER);
		// 写包体
		$packet->WriteInt_N($mid);
		// 结束包体
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			fc::debug(json_encode(array('mem_offline', $mid, $errorcode, $recvLen, $data)), 'socket_err');
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret = $packet->ReadInt_N();
		return $ret;
	}

	/**
	 * 敏感词过滤，如果字符包含敏感词将会替换成♠♣♥♠♣
	 * @var 	$word 		string 		检测的字符串
	 * @var 	$returnStr 	string 		需要server回传的字段
	 * @return  $return 	array 		返回
	 */
	public function dwf_words_trans($word, $returnStr='')
	{
		$word = trim($word);
		if (empty($word))
		{
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::DWF_SERVER);
		// 构建消息包实例
		$packet = by::socketpacket();
		// 写命令字
		$packet->WriteBegin(self::GET_GOOD_WORDS_TRANS);
		$packet->WriteString_N($word);
		$packet->WriteBinary($returnStr,strlen($returnStr));
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$endWord = $packet->ReadString();
		$endStr  = $packet->ReadBinary();
		$status  = $word == $endWord ? true : false;
		return array('status'=>$status,'word'=>$endWord,'return'=>$endStr);
	}

	public function push_msg($mid, $content) {
		$mid = Helper::uint($mid);
		if (empty($mid) || empty($content) || !is_array($content)) {
			return false;
		}
		$str = json_encode($content);
		$this->_initServersConf(self::USER_SERVER, $mid);
		// 构建消息包实例
		$packet = by::socketpacket();
		// 写命令字
		$packet->WriteBegin(self::PHP_PUST_MSG);
		// 写包体
		$packet->WriteInt_N($mid);		
		// 写推送给客户端的内容
		$packet->WriteString_N($str);
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		return true;
	}

	public function serverAdmin($ip, $port, $jsonStr, $isEncry=false)
    {
    	$this->_ip = trim($ip);
    	$this->_port = intval($port);
        // 构建消息包实例
        $packet = by::socketpacket();
        if($isEncry)
        {
            $packet->WriteBegin (self::SYSCMD_BACKDOOR_ENCRY);
        }
        else
        {
            $packet->WriteBegin (self::SYSCMD_BACKDOOR_UNENCRY);
        }
        $endStr = $isEncry ? "encry" : "no_encry";
        $packet->WriteString_N($jsonStr);
        $packet->WriteEnd_n($endStr);
        if (!$this->SendData($this->_ip, $this->_port, $packet))
		{
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false)
		{
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}
		$recv_packet = by::socketpacket();
        $recv_packet->SetRecvPacketBuffer($data,$recvLen);
        $cmd=$recv_packet->GetCmdType();
        if($cmd!=self::SYSCMD_BACKDOOR_UNENCRY)
        {
            return 0;
        }
        $json=$recv_packet->ReadString();
        return $json;
    }

	/**
	 * 取得操作的server配置
	 * @var     $servers 	string  需要请求的Server配置标识
	 * @var 	$mid 		int 	用户id(取模专用)
	 */
	private function _initServersConf($servers, $mid = 0)
	{
		$serverConf = fc::getConfig($servers, 'inc');
		$modid = $mid % count($serverConf);
		if(!isset($serverConf[$modid]))
		{
			$modid = 0;
		}
		$conf = $serverConf[$modid];
		$this->_ip 	 = $conf[0];
		$this->_port = $conf[1];
	}

	/**
	 * 取得PHP映射后的SERVER的GAMEID
	 */
	private function _getServerGameId()
	{
		$gameconfig = fc::getConfig('gameDir');
		return isset($gameconfig[GAMEID][2]) ? intval($gameconfig[GAMEID][2]) : 0;
	}


	/**
	 * 推送红包消息给server
	 * @param int $mid 玩家mid
	 * @param string $info 玩家信息{二人/合集[name:玩家昵称,type:标签,site:位置,time:时间,date:消息内容],四川[name:玩家昵称]}
	 * @param int $id 红包id
	 * @param int $money 红包金额
	 * @param int $num 抢红包人数
	 * @param int $msg 祝福语
	 * @param int $rtype 红包类型: 1金币 2话费券
	 * @param int $sendtype 发放类型: 1随机 2固定
	 * @return int
	 */
	public function push_redenvelope($mid, $info, $id, $rtype, $sendtype, $money, $num) {
		$mid = Helper::uint($mid);
		$id = Helper::uint($id);
		$sendtype = Helper::uint($sendtype);
		$money = Helper::uint($money);
		$num = Helper::uint($num);
		$info = strval($info);
		$rtype = Helper::uint($rtype);
		// 校验非空
		if (empty($mid) || empty($id) || empty($money) || empty($num) || empty($info)) {
			return false;
		}
		// 校验类型
		if (!in_array($rtype, array(1,2)) || !in_array($sendtype, array(1,2))) {
			return false;
		}
		// 校验用户信息
		$infoArr = json_decode($info, true);
		if (empty($infoArr) || !is_array($infoArr)) {
			return false;
		}
		// 取得IP和端口配置
		$this->_initServersConf(self::FHB_SERVER, $mid);
		// 构建消息包实例
		$packet = new SocketPacket();
		// 写命令字
		$packet->WriteBegin(self::SERVER_PUSH_REDENVELOPE);
		//用户mid
		$packet->WriteInt_N($mid);    			
		//玩家昵称
		$packet->WriteString_N($info);         
		//红包id
		$packet->WriteInt_N($id);        		
		//红包类型: 1金币 2话费券
		$packet->WriteInt_N($rtype);       		
		//发放类型: 1随机 2固定
		$packet->WriteInt_N($sendtype);      
		//发放金币数
		$packet->WriteInt_N($money);        	
		//抢红包人数
		$packet->WriteInt_N($num);        		
		// 结束包体
		$packet->WriteEnd_n();
		// 发送包体
		if (!$this->SendData($this->_ip, $this->_port, $packet)) {
			return false;
		}
		// 读取回包
		if (($recvLen = @socket_recv($this->aSockets[$this->_ip][$this->_port], $data, 4096, 0 )) === false) {
			$errorcode = socket_last_error();
			$end_time = fc::microtime_float();
			fc::debug(json_encode(array('push_redenvelope', $mid, $errorcode, $recvLen, $data)), 'socket_err');
			CMCC::getInstance()->report(GAMEID, 'php_mserver_socket', 'socket_recv_err');
			return false;
		}		
		$packet->SetRecvPacketBuffer($data, $recvLen);
		$ret = $packet->ReadInt_N();
		return $ret;
	}	
}