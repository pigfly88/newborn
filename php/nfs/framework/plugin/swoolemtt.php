<?php

/**
 * 主要用于QueneService服务 交互通信接口
 * 
 * @author 
 */
class ModelSwoolemtt{
	public $SwooleTcpIp, $SwooleTcpPort, $act, $client, $sid, $mid;
	private $tryCon = 0;//重心连接次数
	public $aIpPort = array(//正式IP端口  端口 59502 + oo::$config['sid']
		101 => array('10.70.12.131:59603','10.70.12.172:59603'),
		104 => array('10.70.12.172:59606','10.68.111.244:59606'),
		143 => array('10.68.111.244:59645','10.70.12.140:59645'),
		//101 => array('159.253.134.219:59603','37.58.70.176:59603'),
		//104 => array('37.58.70.176:59606','159.253.130.215:59606'),
		//143 => array('159.253.130.215:59645','159.253.130.210:59645'),
	);
	public $aData = array();
	
	public $aIpPort_demo = array(//内网IP端口 端口 8501 + oo::$config['sid']
		101 => array('192.168.202.91:8602','192.168.202.93:8602','192.168.202.94:8602'),
		104 => array('192.168.202.91:8605','192.168.202.93:8605','192.168.202.94:8605'),
		143 => array('192.168.202.91:8644','192.168.202.93:8644','192.168.202.94:8644'),
	);

	public function __construct($sid=0){
		$this->sid = $sid ? (int)$sid : oo::$config['groupSid'];
		if(!PRODUCTION_SERVER){
			$this->aIpPort = $this->aIpPort_demo;
		}
	}
	
	private function connect(){
		if($this->client && $this->client->isConnected()){
			return true;
		}
		if(is_array($this->aIpPort[$this->sid])){
			$sInfo =  $this->aIpPort[$this->sid][array_rand($this->aIpPort[$this->sid], 1)];
		}else{
			return false;
		}
		list($this->SwooleTCPIp, $this->SwooleTCPPort) = explode(':',$sInfo);//随机下
		if(!PRODUCTION_SERVER){
			return $this->asynchronous();
		}
		$client = $this->client = TSwooleClient::CreateClientAndConnect($this->SwooleTCPIp, $this->SwooleTCPPort, 1 , false);
		if($this->client){//1秒超时
			$this->logs('connect OK >>'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort);
			$this->tryCon = 0;
			PRODUCTION_SERVER || $this->usePoll();
			return true;
		}
		$this->tryCon++;
		$this->logs('connect error >>'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort.' tryCon:'.$this->tryCon);
		if($this->tryCon > 3){//重试几次
			return false;
		}
		return $this->connect();
	}
	
	/**
	* 使用异步
	*/
	public function asynchronous(){
		$me = $this;
		$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
		$client->set(array(
			'open_length_check' => 1,
			'package_length_type' => 'n',
			'package_length_offset' => 0, //第N个字节是包长度的值
			'package_body_offset' => 2, //第几个字节开始计算长度
			'package_max_length' => 80000, //协议最大长度
		));
		$client->on("connect", function(swoole_client $cli) use ($me){
			$me->logs('connect OK>>'.$me->SwooleTCPIp.':'.$me->SwooleTCPPort, 7);
		});
		$client->on("receive", function(swoole_client $cli, $data) use ($me){
			$readPackage = new ReadPackageExt();
			$readPackage->ReadPackageBuffer($data);
			$r = $readPackage->ReadByte();
			if($r != 1){//失败的记录下日志
				$me->logs($readPackage->GetCmdType().'>>'.$r, 4);
			}else{
				$me->logs($readPackage->GetCmdType().'>>'.$r, 5);
			}
		});
		$client->on("error", function(swoole_client $cli) use ($me){
			$me->logs('error'.$me->SwooleTCPIp.':'.$me->SwooleTCPPort, 7);
		});
		$client->on("close", function(swoole_client $cli) use ($me){
			$me->logs('close'.$me->SwooleTCPIp.':'.$me->SwooleTCPPort, 7);
		});
		$result = $client->connect($this->SwooleTCPIp, $this->SwooleTCPPort);
		if($result){
			$this->client = $client;
			return true;
		}else{
			$this->logs('connect error'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort, 8);
			return false;
		}
	}
	
	//使用轮询
	public function usePoll(){
		$me = $this;
		$client = $this->client;
		swoole_event_add($client->sock, function($sockId) use ($client, $me){//读取接口
			swoole_event_set($sockId, null, null, SWOOLE_EVENT_WRITE);
			$responseData = $client->recv();
			$readPackage = new ReadPackageExt();
			$readPackage->ReadPackageBuffer($responseData);
			$r = $readPackage->ReadByte();
			if($r != 1){//失败的记录下日志
				$me->logs($readPackage->GetCmdType().'>>'.$r, 4);
			}else{
				$me->logs($readPackage->GetCmdType().'>>'.$r, 5);
			}
		},function($sockId) use ($client, $me){//写接口
			if(!$client->isConnected()){//如果没有连接了
				swoole_event_del($sockId);
				$me->connect();
				$me->logs('swoole_event_add 重连接', 6);
				return;
			}
			if(!$conent = array_shift($me->aData)){
				usleep(500);
				return;
			}
			$sockpack = new WritePackage();
			$sockpack->WriteBegin(0x884);
			$sockpack->WriteString($conent);
			$sockpack->WriteEnd();
			$client->send($sockpack->GetPacketBuffer());
			$me->logs('sockId:'.$sockId.' conent:'.$conent, 7);
			swoole_event_set($sockId, null, null, SWOOLE_EVENT_READ);
		}, SWOOLE_EVENT_WRITE);
	}
	
	/**
	*  udp丢数据给本机处理
	*/
	public function SendToMttSign($aData){
		return oo::swoolequene()->SendToMttInfo(base64_encode(json_encode($aData)).'##'.$this->sid);
	}
	
	/**
	* 发送数据
	* $act 3 报名
	*/
	public function sendMsg($conent){
		PRODUCTION_SERVER or $this->logs('sendMsg:'.$conent.'>>'.$this->sid.'|'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort, 3);
		if(!PRODUCTION_SERVER){//测试异步非阻塞
			if($this->connect()){
				$sockpack = new WritePackage();
				$sockpack->WriteBegin(0x884);
				$sockpack->WriteString($conent);
				$sockpack->WriteEnd();
				$this->client->send($sockpack->GetPacketBuffer());
			}else{
				$this->logs('connectErr>>'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort, 2);
			}
			return;
		}
		
		if(!PRODUCTION_SERVER){//测试事件循环的接口 异步
			if(!$this->client){
				$this->connect();
			}
			$this->aData[] = $conent;
			return;
		}
		if($this->connect()){//同步阻塞
			$readPackage = $this->send($conent);
			if($readPackage){
				$r = $readPackage->ReadByte();
				if($r != 1){//失败的记录下日志
					$this->logs($readPackage->GetCmdType().'>>'.$r.'>>'.$conent, 4);
				}
			}else{
				$this->logs('send Err>>'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort.' errCode:'.socket_strerror($this->client->errCode), 2);
				unset($this->client);
			}
		}else{
			$this->logs('connectErr>>'.$this->SwooleTCPIp.':'.$this->SwooleTCPPort, 2);
		}
	}
	
	/**
	* 写入redis队列
	*/
	public function lpush($fd, $readPack){
		$sData = $readPack->ReadString();
		$aParam = json_decode($sData, true);
		$sig = $aParam['sig'];
		unset($aParam['sig']);
		$this->act = functions::uint($aParam['do']);
		// if($sig != functions::genSimpleSig($aParam)){//验证下
			// return $this->writeBack(2);
		// }
		$length = ocache::redisudp()->lPush( okey::mkudp( 'CBMTT' ), $sData);
		if($length === false){
			return $this->writeBack(3);
		}
		return $this->writeBack(1);
	}
	
	/**
	* 返回数据
	*/
	public function writeBack($result = 1){
		$write = new WritePackage();
		$write->WriteBegin($this->act);
		$write->WriteByte($result);
		SwooleHelper::I()->SendPackage($write);
	}
	
	public function rpop(){
		for( $try = 0; $try < 3; $try++ ){
			$res = (string) ocache::redisudp()->rPop( okey::mkudp( 'CBMTT' ) );
			if( $res ){
				break;
			}
			ocache::redisudp()->close();
		}
		return $res;
	}
	
	/**
	* 收到数据
	*/
	public function doMtt($sData){
		PRODUCTION_SERVER or $this->logs('doMtt>>'.$sData, 1);
		$aParam = json_decode($sData, true);
		$this->act = functions::uint($aParam['do']);
		$this->mid = $mid = functions::uint($aParam['mid']);
		$time = $aParam['time'];
		$unid = functions::uint($aParam['unid']);
		switch($this->act){
			case 1://修改gi
				if (!isset($aParam['tid']) || !isset($aParam['svid']) || !isset($aParam['mtstatus'])) {
					return $this->out(3);
				}
				$tid = functions::uint($aParam['tid']);
				$svid = functions::uint($aParam['svid']);
				$mtstatus = functions::uint($aParam['mtstatus']);
				$giInfo = functions::unserialize(ocache::gi()->get($mid));
				$tmpResult = array('gi' => 0, 'redisgi' => 0);
				if ($giInfo != false) {
					$giInfo['tid'] = $tid;
					$giInfo['svid'] = $svid;
					$giInfo['mtstatus'] = $mtstatus;
					$tmpResult['gi'] = (int) ocache::gi()->set($mid, functions::serialize($giInfo));
				}
				return $this->out(1, $tmpResult);
				break;
			case 2://发奖
				$tid = functions::uint($aParam['tid']);
				$bid = functions::uint($aParam['bid']);
				$rank = functions::uint($aParam['rank']);
				$type = functions::uint($aParam['type']);
				$solevel = functions::uint($aParam['solevel']);
				$rewardMoney = functions::uint($aParam['rewardMoney']);
				$starttime = functions::uint($aParam['starttime']);
				$state = functions::uint($aParam['state']);
				if (!$tid || !$type || !$starttime || !$rank) {
					return $this->out(5);
				}
				$pushStr = sprintf("call AddSnginfo(%d,%d,%d,%d,%d,%d,%d,%d,%d);", $mid, $tid, $bid, $rank, $type, $solevel, $rewardMoney, $starttime, $state);
				oo::proc()->push($pushStr);
				oo::logs()->debug(date("Y-m-d H:i:s") . $mid . "rewardMoney:{$rewardMoney}-starttime:{$rank}", "mttGlRewardError.txt");
				return $this->out(1, array('pushStr' => $pushStr));
				break;
			case 3://报名主服务写参赛资格,主服务只写参赛资格，次服务写参赛资格和人数
				$mtype = functions::uint($aParam['mtype']);
				$sigTime = functions::uint($aParam['sigTime']); //当地时间，要再转成北京时间
				$signType = functions::uint($aParam['signType']);
				$num = functions::uint($aParam['num']);
				$endtime = functions::uint($aParam['endtime']);
				$ddcard = functions::uint($aParam['ddcard']);
				$type = functions::uint($aParam['type']);
				$svid = functions::uint($aParam['svid']);
				if (!$mtype || !$sigTime || !$svid || strlen((string) $mid) < 9) {
					return $this->out(8);
				}
				if ($signType == 0) {//钱报名 貌似没有什么参数是必须的。。。。
				} else {//参赛券报名 道具id 数量都是必须的
					if (!$num || !$ddcard) {
						return $this->out(9);
					}
				}
				if (is_array($aParam['redisGi'])) {
					$key = okey::gi($mid, 0);
					ocache::redisgi()->hMset($key, (array) $aParam['redisGi']);
				}
				$signInfo = array('signType' => $signType, 'num' => $num, 'endtime' => $endtime, 'ddcard' => $ddcard);
				$hourDiff = 3600 * oo::matchnew()->getHourDiff();
				$sigTime_for_c = $sigTime + $hourDiff;
				$midInfo = functions::getUserOldMidSid($mid);
				if ($midInfo == false) {
					return $this->out(9);
				}
				if($type==1){
					$t = (int)oo::$config['mttmatchnew'][$mtype]['delayTime'];
					$keyMtt = okey::mDelaySignUp($mid, $svid);
					$kMtt = okey::mDelayStateSignUp($mid, $svid);
					ocache::mttsig()->set($keyMtt, 1, $t);
					ocache::mttsig()->set($kMtt, 1, $t);
					$MttJoinNumkey = okey::mMttJoinNum($svid, $sigTime);
					$allcount =(int)ocache::mttsig()->get($MttJoinNumkey);//参赛人数
					ocache::mttsig()->set($MttJoinNumkey,$allcount+1);
				} 
				$retInfo = array('sigInfo' => '', 'signCount' => '', 'ticketCount' => '', 'rewardInfo' => '', 'cacheSignUp' => '');
				$retInfo['sigInfo'] = oo::mttuser()->saveSigInfo($mid, $mtype, $sigTime_for_c, $signInfo, $svid); //更新报名mysql数据表
				if ($retInfo['cacheSignUp'] = oo::matchnew()->cacheSignUp($mid, date('Y-m-d H:i:s', $sigTime_for_c), $mtype, $sigTime, $type)) { //更新报名cache
					if ($midInfo['sid'] != oo::$config['groupSid']) {//不是主服才写
						$retInfo['signCount'] = oo::matchnew()->incCurSignCount($sigTime, 1, $mtype); //累加其报名人数，缓存4天
						$signInfo['signType'] == 1 && $signInfo['ddcard'] && ($retInfo['ticketCount'] = oo::matchnew()->incTicketCount($sigTime_for_c, 1, $mtype, $svid)); //记录参赛卷报名的人数
						$retInfo['cacheSignUp'] = oo::matchnew()->currentRewardInfo($mtype, $sigTime, $svid);
					}
				}
				return $this->out(1, $retInfo);
				break;
			case 4://退费未参赛自动退费
				$sigTime = functions::uint($aParam['sigTime']); //当地时间
				$svid = functions::uint($aParam['svid']);
				$success = functions::uint($aParam['success']);
				$aMid = $aParam['aMid'];
				if (!$sigTime || !$svid) {//|| !$aMid || !is_array($aMid)不判断$aMid没有一个人进入比赛的时候也要通知退费
					return $this->out(10);
				}
				$midStr = implode(',', $aMid);
				$key = okey::mMttNotJoinRet($svid, $sigTime);
				$retInfo = array('setCache' => '', 'addProc' => '');
				$midStr && $retInfo['setCache'] = ocache::mttsig()->set($key, $midStr, 3600 * 4);
				$allcount = functions::uint($aParam['allcount']);
				$MttJoinNumkey = okey::mMttJoinNum($svid, $sigTime);
				(int)ocache::mttsig()->set($MttJoinNumkey,$allcount);//参赛人数
				$pushStr = sprintf("call AddMttInfoMatchStart(%d,%d,%d);", $sigTime, $svid, $success);
				$retInfo['addProc'] = oo::proc()->push($pushStr);
				return $this->out(1, $retInfo);
				break;
			case 5://报名或退赛增加减人数
				$svid = functions::uint($aParam['svid']);
				$sigTime = functions::uint($aParam['sigTime']); //当地时间，要转北京时间
				$mtype = functions::uint($aParam['mtype']);
				$isSign = functions::uint($aParam['isSign']); //1报名 做增加人数操作， 2 退赛 做减少人数操作
				$signType = functions::uint($aParam['signType']);
				$ddcard = functions::uint($aParam['ddcard']);
				if (!$svid || !$sigTime || !$isSign || !$mtype) {
					return $this->out(11);
				}
				if (empty(oo::$config['mttmatchnew'][$mtype]['groupid'])) {
					return $this->out(12);
				}
				$hourDiff = 3600 * oo::matchnew()->getHourDiff();
				$sigTime_for_c = $sigTime + $hourDiff;
				$retInfo = array('signCount' => '', 'ticketCount' => '', 'rewardInfo' => '');
				if ($isSign == 1) {//报名 增加人数
					$MttJoinNumkey = okey::mMttJoinNum($svid, $sigTime);
					$allcount = (int)ocache::mttsig()->get($MttJoinNumkey);//参赛人数
					ocache::mttsig()->set($MttJoinNumkey,$allcount+1);//参赛人数
					$retInfo['signCount'] = oo::matchnew()->incCurSignCount($sigTime, 1, $mtype); //累加其报名人数，缓存4天
					$signType == 1 && $ddcard && ($retInfo['ticketCount'] = oo::matchnew()->incTicketCount($sigTime_for_c, 1, $mtype, $svid)); //记录参赛卷报名的人数
				} elseif ($isSign == 2) {//退赛 减少人数
					$retInfo['signCount'] = oo::matchnew()->incCurSignCount($sigTime, -1, $mtype);
					$signType == 1 && $ddcard && ($retInfo['ticketCount'] = oo::matchnew()->incTicketCount($sigTime_for_c, -1, $mtype, $svid)); //记录参赛卷报名的人数
				}
				$retInfo['rewardInfo'] = oo::matchnew()->currentRewardInfo($mtype, $sigTime, $svid);
				return $this->out(1, $retInfo);
				break;
			case 6://退费主动退费
				$sid = functions::uint($aParam['sid']);
				$mtype = functions::uint($aParam['mtype']);
				$sigTime = functions::uint($aParam['sigTime']); //当地时间
				$svid = functions::uint($aParam['svid']);
				$signType = functions::uint($aParam['signType']);
				if (!$sid || !$mtype || !$sigTime || !$svid) {
					return $this->out(13);
				}
				$hourDiff = 3600 * oo::matchnew()->getHourDiff();
				$sigTime_for_c = $sigTime + $hourDiff;
				$retArr = array('delCache' => '', 'delTicketCache' => '', 'rewardInfo' => '');
				$retArr['delCache'] = oo::matchnew()->callDelSigCache($mid, $mtype, $sigTime, false, true);
				$retArr['signCount'] = oo::matchnew()->incCurSignCount($sigTime, -1, $mtype);
				$signType == 1 && ($retArr['delTicketCache'] = oo::matchnew()->incTicketCount($sigTime_for_c, -1, $mtype, $svid)); //参赛卷报名人数减1
				$retArr['rewardInfo'] = oo::matchnew()->currentRewardInfo($mtype, $sigTime, $svid);
				return $this->out(1, $retArr);
				break;
			case 7://未开赛退费
				$sigTime = functions::uint($aParam['sigTime']); //当地时间
				$svid = functions::uint($aParam['svid']);
				$type = functions::uint($aParam['type']);
				$reason = $aParam['reason'];
				if (!$sigTime || !$svid || !$type) {
					return $this->out(14);
				}
				$pushStr = sprintf("call AddChapionNoStart(%d,%d,%d,%d);", $svid, $sigTime, $type, $reason);
				$retInfo['addProc'] = oo::proc()->push($pushStr);
				return $this->out(1, $retInfo);
				break;
			case 8: //写主站GI信息
				$giInfo = $aParam['giInfo'];
				if (!$giInfo || !$mid) {
					return $this->out(15);
				}
				oo::logs()->debug(date("Y-m-d H:i:s") . $mid . "giInfo:{$giInfo}-starttime:{$rank}", "redisGiDebug.txt.txt");
				$giInfo['csid'] = $giInfo['sid'];
				$giInfo['sid'] = oo::$config['groupSid'];
				$tmpResult['gi'] = (int) ocache::gi()->set($mid, functions::serialize($giInfo));
				return $this->out(1, $tmpResult);
				break;
			case 9:
				$svid = functions::uint($aParam['svid']);
				$starttime = $aParam['starttime'];
				$moneyring = $aParam['moneyring'];
				$leftplaynum = $aParam['leftplaynum'];
				$LeftPNumkey = okey::mMttLeftPNum($svid, $starttime);
				$mkmoneyringkey = okey::mkmoneyring($svid, $starttime);
				$setarray[$LeftPNumkey] = $leftplaynum;
				$setarray[$mkmoneyringkey] = $moneyring;
				ocache::mttSig()->setMulti($setarray);
				return $this->out(1, $setarray);
				break;
			case 10:
				$sigTime_for_c  = functions::uint($aParam['sigTime_for_c']);
				$sigTime = functions::uint($aParam['sigTime']);
				$mtype = functions::uint($aParam['mtype']);
				$signSid  = functions::uint($aParam['mtype']);
				$arraymid = $aParam['arraymid'];
				foreach( (array) $arraymid as $mid){
					$newmid = functions::genUserNewMid($mid, $signSid);
					oo::matchnew()->cacheSignUp($newmid, date('Y-m-d H:i:s', $sigTime_for_c), $mtype, $sigTime, 0);
				}
				break;
		}
		return $this->out(1);
	}
	
	/**
	* 统一返回数据
	*/
	private function out($res, $aD = array()){
		if($res != 1){//错误的记录下
			$this->logs($this->mid.'>>'.$this->act.'>>'.$res.'>>'.json_encode($aD), 5);
		}
		return $this->act.'|'.$res;
	}
	
	private function send($conent){
		$sockpack = new WritePackage();
		$sockpack->WriteBegin(0x884);
		$sockpack->WriteString($conent);
		$sockpack->WriteEnd();
		$readPackage = TSwooleClient::SendAndReciveByClient($this->client, $sockpack->GetPacketBuffer());
		return $readPackage;
	}
	
	private function logs($con, $ty=0){
		oo::logs()->debug(date('Y-m-d H:i:s ').$con, 'swooleMtt'.$ty.'.txt');
	}
	/**
	 *  模仿callBack 处理相关数据
	 */
	public function doMttBack(){		
		if (!defined('TSWOOLE')) {
			return false;
		}
		$count = 100;
		while ($count > 0) {
			$count--;
			$sdata = $this->rpop();
			$sdata = trim($sdata);
			if (!$sdata) {
				return;
			}
			$begin_microTime = microtime(true);
			$begin_usemem = memory_get_usage(1);
			$func = $this->doMtt($sdata);
			SwooleModelcrontab::SaveMonitorInfo($begin_microTime, "mttback|" . $func,$begin_usemem);
		}
	}

}
