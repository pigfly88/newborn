<?php

include_once(dirname( __FILE__ ) . "/EServer_EncryptDecrypt.php");
include_once(dirname( __FILE__ ) . "/EServer_ISocketPacket.php");

class EServer implements EServer_ISocketPacket{

	const SERVER_PACEKTVER = 2;
	const SERVER_SUBPACKETVER = 1;
	const PACKET_BUFFER_SIZE = 8192;
	const PACKET_HEADER_SIZE = 9;

	private $cservers;
	private $svip;
	private $svport;
	private $socket;
	private $m_packetBuffer;
	private $m_packetSize;
	private $m_CmdType;
	private $m_cbCheckCode;
	private $beginTime;//上报时间用

	public function __construct( $cservers ){
		$this->cservers = $cservers;

		$cserver = $this->cservers[rand( 0, count( $cservers ) - 1 )]; //随机获取一个

		$this->svip = $cserver[0];
		$this->svport = $cserver[1];

		$this->m_packetSize = 0;
		$this->m_packetBuffer = "";
		
		$this->beginTime = microtime(true);
	}

	public function WriteBegin( $CmdType ){
		$this->m_CmdType = $CmdType;
		$this->m_packetSize = 0;
		$this->m_packetBuffer = "";
	}

	public function ConnectServer(){
		if( (!$this->svip ) || (!$this->svport ) ){
			return false;
		}

		$this->socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
		if( $this->socket < 0 ){
			return false;
		}
		$ret = false;
		try{
			socket_set_option( $this->socket, SOL_SOCKET, SO_RCVTIMEO, array( "sec" => 3, "usec" => 0 ) );
			socket_set_option( $this->socket, SOL_SOCKET, SO_SNDTIMEO, array( "sec" => 1, "usec" => 0 ) );
			socket_set_block($this->socket);
			$ret = socket_connect( $this->socket, $this->svip, $this->svport ) < 0 ? false : true;
		} catch( Exception $e ){

		}
		return $ret;
	}

	public function WriteEnd(){
		$EncryptObj = new EServer_EncryptDecrypt();
		$code = $EncryptObj->EncryptBuffer( $this->m_packetBuffer, 0, $this->m_packetSize );
		$len = $this->m_packetSize + 5;
		$this->m_packetSize += 7;
		$content = "BY";
		$content .= pack( "c", self::SERVER_PACEKTVER );  //ver
		$content .= pack( "n", $this->m_CmdType );   //cmd
		$head = pack( "n", $len );
		$this->m_packetBuffer = $head . $content . $this->m_packetBuffer;
	}

	private function to_hex_str( $num ){
		$str = dechex( $num );

		return str_repeat( '0', 4 - strlen( $str ) ) . $str;
	}

	public function GetPacketBuffer(){
		return $this->m_packetBuffer;
	}

	public function GetSocket(){
		return $this->socket;
	}

	public function SendData( $large=false ){
		if( $large ) {
			$length = strlen( $this->m_packetBuffer);
			$psends = 1024;
			$offset = 0;
			while( $offset < $length ) {
				$sent = socket_send( $this->socket, substr( $this->m_packetBuffer, $offset, $psends ), $psends, MSG_DONTROUTE  );
				if( !$sent ) break;
				$offset += $sent;
			}
			if( $offset < $length ) return 0;
			return $offset;
		}
		return socket_write( $this->socket, $this->m_packetBuffer, 4096 );
	}

	public function CloseSocket(){
		socket_close( $this->socket );
	}

	public function GetPacketSize(){
		return $this->m_packetSize;
	}

	public function WriteInt( $value ){
		$this->m_packetBuffer .= pack( "N", $value );
		$this->m_packetSize += 4;
	}

	public function WriteUInt( $value ){
		$this->m_packetBuffer .= pack( "N", $value );
		$this->m_packetSize += 4;
	}

	public function WriteByte( $value ){
		$this->m_packetBuffer .= pack( "C", $value );
		$this->m_packetSize += 1;
	}

	public function WriteShort( $value ){
		$this->m_packetBuffer .= pack( "n", $value );
		$this->m_packetSize += 2;
	}

	public function WriteString( $value ){
		$len = strlen( $value ) + 1;
		$this->m_packetBuffer .= pack( "N", $len );
		$this->m_packetBuffer .= $value;
		$this->m_packetBuffer .= pack( "C", 0 );
		$this->m_packetSize += $len + 4;
	}

	public function ParsePacket(){
		if( $this->m_packetSize < self::PACKET_HEADER_SIZE ){
			return false;
		}

		$Len = $this->ReadShort();
		$header = substr( $this->m_packetBuffer, 0, 5 );
		$arr = unpack( "c2Iden/cVer/sCmdType", $header );
		if( $arr['Iden1'] != ord( 'B' ) || $arr['Iden2'] != ord( 'Y' ) ){
			return -1;
		}
		if( $arr['Ver'] != self::SERVER_PACEKTVER ){
			return -2;
		}
		if( $arr['CmdType'] <= 0 || $arr['CmdType'] >= 32000 ){
			return -3;
		}
		$this->m_packetBuffer = substr( $this->m_packetBuffer, 5 );

		$DecryptObj = new EServer_EncryptDecrypt();
		$code = $DecryptObj->DecryptBuffer( $this->m_packetBuffer, $Len - 5, 0 );

		return 0;
	}

	public function SetRecvPacketBuffer( $packet_buff, $packet_size ){
		$this->m_packetBuffer = $packet_buff;
		$this->m_packetSize = $packet_size;
	}

	public function ReadInt(){
		$temp = substr( $this->m_packetBuffer, 0, 4 );
		$value = unpack( "N", $temp );
		$this->m_packetBuffer = substr( $this->m_packetBuffer, 4 );
		return $value[1];
	}

	public function ReadUInt(){
		$temp = substr( $this->m_packetBuffer, 0, 4 );
		list(, $var_unsigned) = unpack( "L", $temp );
		return floatval( sprintf( "%u", $var_unsigned ) );

		$value = unpack( "L", $temp );
		$this->m_packetBuffer = substr( $this->m_packetBuffer, 4 );
		return $value[1];
	}

	public function ReadShort(){
		$temp = substr( $this->m_packetBuffer, 0, 2 );
		$value = unpack( "n", $temp );
		$this->m_packetBuffer = substr( $this->m_packetBuffer, 2 );
		return $value[1];
	}

	public function ReadString(){
		$len = $this->ReadInt();
		$value = substr( $this->m_packetBuffer, 0, $len );
		$this->m_packetBuffer = substr( $this->m_packetBuffer, $len );
		return $value;
	}

	public function ReadByte(){
		$temp = substr( $this->m_packetBuffer, 0, 1 );
		$value = unpack( "C", $temp );
		$this->m_packetBuffer = substr( $this->m_packetBuffer, 1 );
		return $value[1];
	}

	public function sendMsg( $msg ,$isUdp = true){
	    
		$beginTime = microtime(true);//上报用时间
	    
		if($isUdp){
			$this->WriteBegin( 0x104 );

			$this->WriteString( $msg );
			$this->WriteEnd();

		    return $this->udpSend();//通过udp转发
		}

		if( !$this->ConnectServer() ){
			return 0;
		}

		$this->WriteBegin( 0x104 );

		$this->WriteString( $msg );
		$this->WriteEnd();

		$sendLen = $this->SendData();
		if( in_array( oo::$config['sid'],array( 110, 67 ) ) ){//对启用了小喇叭代理的平台接收回包，保证数据到达
			$this->m_packetBuffer = "<policy-file-request/>\x00";
			$this->SendData();
			$buf = '';
			socket_recv($this->socket, $buf, 200, 0);
		}
		$this->CloseSocket();
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('sendMsg')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('sendMsg')), 1, count($log), '', $addr, $endTime);
		}
		
		return $sendLen;
	}

	//指定用户发JS广播
	public function sendJsMsg($mid, $msg, $isUdp = true){
	    
		$beginTime = microtime(true);//上报用时间

		if($isUdp){
			$this->WriteBegin( 0x10E );
			$this->WriteInt( $mid );
			$this->WriteString( 'cade073b2c1b6612db735a41c11853f4' );
			$this->WriteString( rawurlencode($msg) );
			$this->WriteEnd();

			return $this->udpSend();//通过udp转发
		}

		if( !$this->ConnectServer() ){
			return 0;
		}

		$this->WriteBegin( 0x10E );
		$this->WriteInt( $mid );
		$this->WriteString( 'cade073b2c1b6612db735a41c11853f4' );
		$this->WriteString( rawurlencode($msg) );
		$this->WriteEnd();
		$sendLen = $this->SendData();
		if( in_array( oo::$config['sid'],array( 110, 67 ) ) ){//对启用了小喇叭代理的平台接收回包，保证数据到达
			$this->m_packetBuffer = "<policy-file-request/>\x00";
			$this->SendData();
			$buf = '';
			socket_recv($this->socket, $buf, 200, 0);
		}
		$this->CloseSocket();
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('sendJsMsg')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('sendJsMsg')), 1, count($log), '', $addr, $endTime);
		}
		
		return $sendLen;
	}

	//全服发JS广播
	public function sendJsMsgAll($msg, $isUdp = true){
		
		$beginTime = microtime(true);//上报用时间
	    
		$data = array(
			'type' => 100,
			'test' => PRODUCTION_SERVER ? 0 : 1,
			'js' => rawurlencode($msg),
		);
		oo::EServer()->sendMsg(json_encode($data),$isUdp);
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('sendJsMsgAll')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('sendJsMsgAll')), 1, count($log), '', $addr, $endTime);
		}
	}

	/**
	 * 对全桌玩家发送JS推送
	 *
	 * @param Int $tid 桌子ID
	 * @param Json $msg 消息，js代码
	 * @param Int $stat 过滤状态，-1=全桌用户，1坐下状态的用户，0站起用户
	 * @return int
	 */
	public function sendJsMsgByTid($tid, $msg, $stat = -1, $isUdp = true){
	    
		$beginTime = microtime(true);//上报用时间
	    
		if($isUdp){
			$this->WriteBegin( 0x10F );
			$this->WriteInt( $tid );
			$this->WriteString( 'c801792bc8959b4842f526e8dc11b322' );
			$this->WriteShort($stat);
			$this->WriteString( rawurlencode($msg) );
			$this->WriteEnd();

			return $this->udpSend();//通过udp转发
		}

		if( !$this->ConnectServer() ){
			return 0;
		}
		if( ($tid = functions::uint( $tid )) <= 0 ){
			return 0;
		}

		$this->WriteBegin( 0x10F );
		$this->WriteInt( $tid );
		$this->WriteString( 'c801792bc8959b4842f526e8dc11b322' );
		$this->WriteShort($stat);
		$this->WriteString( rawurlencode($msg) );
		$this->WriteEnd();
		$sendLen = $this->SendData();
		if( in_array( oo::$config['sid'],array( 110, 67 ) ) ){//对启用了小喇叭代理的平台接收回包，保证数据到达
			$this->m_packetBuffer = "<policy-file-request/>\x00";
			$this->SendData();
			$buf = '';
			socket_recv($this->socket, $buf, 200, 0);
		}
		$this->CloseSocket();
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('sendJsMsgByTid')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('sendJsMsgByTid')), 1, count($log), '', $addr, $endTime);
		}
		
		return $sendLen;
	}

	/**
	 * 批量获取指定mid的在线状态(每次不超过1000个ID)
	 *
	 * @param array $aMids
	 * @return array 格式 array(mid=>stat),stat:0=离线，1=大厅，2=旁观，3=在玩
	 */
	public function getUsersStat(array $aMids = array()){
	    
		$beginTime = microtime(true);//上报用时间
	    
		if(empty($aMids) || !$this->ConnectServer()){
			return array();
		}

		$this->WriteBegin(0x110);

		$iCnt = 0;
		foreach($aMids as $mid){
			if(($mid = intval($mid)) > 0){
				$this->writeInt($mid);
				$iCnt++;
			}
			if($iCnt > 999){
				break;
			}
		}

		$this->WriteEnd();
		$iSendLen = $this->SendData();
		if($iSendLen <= 0){
			return array();
		}

		$sBuffer = '';
		socket_recv($this->socket, $sBuffer, 5120, 0);

		if(($iLen = strlen($sBuffer)) < 7){
			return array();
		}

		$this->SetRecvPacketBuffer($sBuffer, $iLen);

		if($this->ParsePacket() != 0){
			return array();
		}

		$aStats = array();
		while(strlen($this->m_packetBuffer) > 0){
			$mid = intval($this->ReadInt());
			$aStats[$mid] = intval($this->ReadByte());
		}

		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('getUsersStat')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('getUsersStat')), 1, count($log), '', $addr, $endTime);
		}
		
		return $aStats;
	}

	public function sendSingleMsg( $fmid, $tmid, $msg, $large=false,$isUdp = true ){
		
		$beginTime = microtime(true);//上报用时间
	    
		if($isUdp){
			$this->WriteBegin( 0x103 );

			$this->WriteInt( $fmid );
			$this->WriteInt( $tmid );

			$this->WriteString( $msg );
			$this->WriteEnd();

			return $this->udpSend();//通过udp转发
		}

		if( !$this->ConnectServer() ){
			return 0;
		}

		$this->WriteBegin( 0x103 );

		$this->WriteInt( $fmid );
		$this->WriteInt( $tmid );

		$this->WriteString( $msg );
		$this->WriteEnd();

		$sendLen = $this->SendData( $large );
		if( in_array( oo::$config['sid'],array( 110, 67 ) ) ){//对启用了小喇叭代理的平台接收回包，保证数据到达
			$this->m_packetBuffer = "<policy-file-request/>\x00";
			$this->SendData();
			$buf = '';
			socket_recv($this->socket, $buf, 200, 0);
		}
		$this->CloseSocket();
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('sendSingleMsg')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('sendSingleMsg')), 1, count($log), '', $addr, $endTime);
		}
		
		return $sendLen;
	}
	
	/**
	 * 
	 * @param type $tid 对应的桌子id
	 * @param type $type 1获取坐下在玩的玩家 2旁观 3所有
	 */
	public function getUserStatByTid($tid,$type = 3){
		$tid = functions::uint($tid);
		$type = functions::uint($type);
		$ret = array();
		
		if(!$tid || !in_array($type,array(1,2,3))){
			return $ret;
		}
		
		if(!$this->ConnectServer()){
			return $ret;
		}
		
		$this->WriteBegin( 0x887 );	//
		
		$this->WriteInt( $tid );
		$this->WriteInt( $type );
		
		$this->WriteEnd();
		
		$sendLen = $this->SendData();

		$recvLen = socket_recv( $this->socket, $buf, 5120, 0 );
		$this->SetRecvPacketBuffer( $buf, $recvLen );
		$data = strval( $this->ParsePacket() == 0 ? $this->ReadString() : "" );
		
		$tableInfo = json_decode($data,true);
		$this->CloseSocket();
		
		return $tableInfo;
	}
	
		/**
	 * 
	 * @param type $tid 对应的桌子id
	 * @param type $type 1获取坐下在玩的玩家 2旁观 3所有
	 */
	public function getUserStatByTidMulit($tids,$type = 3){
		$tids = is_array($tid) ? $tids : (array)$tids;
		$type = functions::uint($type);
		$ret = array();
		
		if(empty($tids) || !in_array($type,array(1,2,3))){
			return $ret;
		}
		
		if(!$this->ConnectServer()){
			return $ret;
		}
		
		$this->WriteBegin( 0x886 );	//
		
		$tidsStr = implode('|', $tids);
		
		$this->WriteString( $tidsStr );
		$this->WriteInt( $type );
		
		$this->WriteEnd();
		
		$sendLen = $this->SendData();

		$recvLen = socket_recv( $this->socket, $buf, 5120, 0 );
		$this->SetRecvPacketBuffer( $buf, $recvLen );
		$data = strval( $this->ParsePacket() == 0 ? $this->ReadString() : "" );
		
		$tableInfo = json_decode($data,true);
		$this->CloseSocket();
		
		return $tableInfo;
	}
	
	

	/**
	 * 请求在线用户数
	 */
	public function getCount($onlyNumber = true){
		
		$beginTime = microtime(true);//上报用时间
		
		if(in_array(oo::$config['sid'],array(57))){
		    $tSvip = $this->svip;
		    $tSvport = $this->svport;
		    
		    $count = array('total'=>0,'svr'=>array());
		   
		    
		    foreach($this->cservers as $key=>$cserver){	
			if(!is_array($cserver)){
			    continue;
			}
			$this->svip = $cserver[0];
			$this->svport = $cserver[1];
			
			if( !$this->ConnectServer() ){
			    continue;
			}

			$this->WriteBegin( 0x109 );	//先发请求在线人数
			$this->WriteEnd();
			$sendLen = $this->SendData();

			$recvLen = socket_recv( $this->socket, $buf, 64, 0 );
			$this->SetRecvPacketBuffer( $buf, $recvLen );
			
			$count['svr'][$key] = array($this->svip,$this->svport,0);
			$count['svr'][$key][2] = $this->ParsePacket() == 0 ? $this->ReadInt() : 0;
			$count['total'] += $count['svr'][$key][2];
			
			//echo "\n",$this->svip,'----',$this->svport,'-----',$t,"\n;";
			$this->CloseSocket();
		    }
		    
		    $this->svip = $tSvip;
		    $this->svport = $tSvport;
		    
		    if($onlyNumber){
			$count = $count['total'];
		    }
		    
		    //return $count;
		}else{
		    if( !$this->ConnectServer() ){
			    return 0;
		    }

		    $this->WriteBegin( 0x109 );	//先发请求在线人数
		    $this->WriteEnd();
		    $sendLen = $this->SendData();

		    $recvLen = socket_recv( $this->socket, $buf, 64, 0 );
		    $this->SetRecvPacketBuffer( $buf, $recvLen );

		    $count = (int)($this->ParsePacket() == 0 ? $this->ReadInt() : 0);
		    $this->CloseSocket();
		}

		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('getCount')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('getCount')), 1, count($log), '', $addr, $endTime);
		}
		
		return  $count;
	}

	/**
	 * 获取server运行时系统信息
	 */
	public function getSysInfo(){
		$beginTime = microtime(true);//上报用时间
		
		if( !$this->ConnectServer() ){
			return 0;
		}
		
		if(in_array(oo::$config['sid'],array(57))){
		    $str = array();
		    $count = 0;
		    
		    $tSvip = $this->svip;
		    $tSvport = $this->svport;
			
		    foreach($this->cservers as $key=>$cserver){

			$this->svip = $cserver[0];
			$this->svport = $cserver[1];
			
			if( !$this->ConnectServer() ){
			    continue;
			}
			
			$this->WriteBegin( 0x888 );
			$this->WriteString( "f35537b335a767c5b60d76863daff7af" );
			$this->WriteEnd();
			$sendLen = $this->SendData();
			$recvLen = socket_recv( $this->socket, $buf, 5120, 0 );
			$this->SetRecvPacketBuffer( $buf, $recvLen );
			$str[$count] = array('ip'=>$this->svip,'port'=>$this->svport,'status'=>'');
			$str[$count]['status'] = strval( $this->ParsePacket() == 0 ? $this->ReadString() : "" );
			$this->CloseSocket();
			$count++;
		    }
		    
		    $this->svip = $tSvip;
		    $this->svport = $tSvport;
		    
		}else{
		    
		    if( !$this->ConnectServer() ){
			    return 0;
		    }
		    
		    $this->WriteBegin( 0x888 );
		    $this->WriteString( "f35537b335a767c5b60d76863daff7af" );
		    $this->WriteEnd();
		    $sendLen = $this->SendData();
		    $recvLen = socket_recv( $this->socket, $buf, 5120, 0 );
		    $this->SetRecvPacketBuffer( $buf, $recvLen );

		    $str = strval( $this->ParsePacket() == 0 ? $this->ReadString() : "" );
		    $this->CloseSocket();
		}
		
		if( PRODUCTION_SERVER && oo::$config['sid']===57 && is_file( PATH_LIB . 'class.statistic.php')){
		    $endTime = microtime(true);
		    include_once PATH_LIB . 'class.statistic.php';
		    StatisticClient::tick("api", implode(',', array('getSysInfo')), $beginTime);
		    $addr = SERVER_TYPE == 'demo' ? 'udp://192.168.97.50:55656' : 'udp://175.45.32.183:55656';
		    StatisticClient::report('api', implode(',', array('getSysInfo')), 1, count($log), '', $addr, $endTime);
		}
		
		
		return  $str;
	}
	
	
	/**
	 * 发送异步消息
	 */
	public function udpSend( ){

		$socket = @socket_create( AF_INET, SOCK_DGRAM, SOL_UDP );
		if( $socket === false ){
			oo::logs()->debug( socket_strerror( socket_last_error() ) . '-' . __LINE__ . '-' . $this->svip . ': '. $this->svport , 'EserverUdp.txt' );
			return false;
		}
		if( ($result = @socket_sendto( $socket, $this->m_packetBuffer, strlen( $this->m_packetBuffer ), MSG_EOF, $this->svip, $this->svport )) === false ){ //发送失败
			oo::logs()->debug( socket_strerror( socket_last_error() ) . '-' . __LINE__ . '-' . $this->svip . ': '. $this->svport , 'EserverUdp.txt' );
			return false;
		}
		return true;
	}
	
	
	
}
