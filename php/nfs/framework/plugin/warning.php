<?php
!defined('BOYAA') AND exit('Access Denied!');
/**
 * 报警提示
 * @author BarryZhu 20150724
 *
 */
class warning{
	const REDIS_PORT = 'warning';
	const KEY_MSG = 'warning:msg'; //预警队列
	const KEY_INFO = 'warning:info'; //详情
	const KEY_LIST = 'warning:list'; //消息列表，用于后台展示
	const FREQ_TIME = 60; //多久之内不重复报警，单位（秒）
	protected static $cfg = null; //报警配置
	
	//不需要订阅自动发消息提醒
	public static $autotype = array(
			'phperr.txt', 'payerr.txt', 'rediserr.txt','memcachederr.txt', 
			'money', 'server',  'mysql', 'account',
	);
	
	
	protected static function getcfg(){
		is_null(self::$cfg) && self::$cfg = fc::getDeploy('warning', 'base');
		return self::$cfg;
	}
	
	/**
	 * 加入预警队列
	 * @param string $msg 消息
	 * @param string $type 标识
	 * @param string $detail 详细信息
	 */
	public static function add($msg, $type='default', $extend=array()){
		if(self::check_ignore($msg)){ //手动忽略错误
			return 0;
		}
		$msg = str_replace(array("\r", "\n", "\t"), array(""), $msg);
		if(isset($extend['trace'])){//记录trace
			$tracetype = intval($extend['trace']);
			$trace = self::get_trace($tracetype); //0-上一个trace，1-完整的trace
			$msg .= $trace;
		}

		if(ENVID!=3 && !$_REQUEST['test']){//非正式环境不报警
			return 0;
		}
		
		$cfg = self::getcfg();
		if(!$cfg['warning_switch']){ //报警总开关
			return 0;
		}
		if(!isset($cfg[$type]) && !in_array($type, self::$autotype)){ //没有订阅的不报警
			return 0;
		}

		$res = false;
		$now = time();
		$redis = by::redis(self::REDIS_PORT);
		
		$original_msg = $msg;
		$msg = self::msg_implode($type, $msg);
		$hashkey = self::genhashkey($msg);
		$key = self::KEY_INFO.':'.$hashkey;
		$info = $redis->hgetall($key);

		//加入消息队列
		if($redis->llen(self::KEY_MSG)>=10000){ //控制消息队列长度
			return false;
		}
		
		//同样的错误一分钟之内多次报警，则添加重复报警次数提示
		//相隔一分钟以上的，控制报警频率
		$data = array(
				//'ip'=>Helper::getIp(),
				//'request'=>json_encode($_REQUEST),
				//'trace' => self::get_trace(),
		);
		if($now-$info['time']>self::FREQ_TIME){
			$total = intval($info['total']);
			$total_tmp = intval($info['total_tmp']);
			
			if($total-$total_tmp>1){
				$repeat_times = intval($info['total'])-intval($info['total_tmp']);
				$msg .= " [重复报警".$repeat_times."次]";
			}
			$ignore = false;
			if($info['count']>5 && $now-$info['time']<600){ //报警频繁的，控制报警频率
				$ignore = true;
			}
			if(!$ignore){
				if($redis->lPush(self::KEY_MSG, $msg, false, false)){
					$redis->hincrby($key, 'count', 1); //累加当天实际报警次数
					$data['time'] = $now;
					$data['total_tmp'] = intval($info['total']);
					$res = true;
				}
			}
		}
		/*elseif($count >= self::FREQ_COUNT){ //控制时间段内的报警频率
			$redis->hset($key, 'count', 0);
			$msg .= " [重复报警{$count}次]";
			if($redis->lPush(self::KEY_MSG, $msg, false, false)){
				$res = true;
			}
		}
		*/
		
		//存详情
		empty($info['msg']) && $data['msg'] = $msg;
		$redis->hmset($key, $data);
		$redis->hincrby($key, 'total', 1); //累加当天报警次数
		
		!$info && $redis->expireAt($key, strtotime('tomorrow'));
		$redis->zincrby(self::KEY_LIST, 1, $key);
		$redis->expireAt(self::KEY_LIST, strtotime('tomorrow'));
		
		if(!$res){
			$res = fc::debug("{$original_msg}", self::getlogfilename($type));//存日志文件方式
		}

		return $res;
	}
	
	public static function get($nums=100){
		$redis = by::redis(self::REDIS_PORT);
		$count=1;		
		while($data = $redis->rPop(self::KEY_MSG, false, false)){
			$warning[] = $data;
			if($count>=$nums)	break;
			$count++;
		}
		return $warning;
	}
	
	public function info($key){
		$redis = by::redis(self::REDIS_PORT);
		$key = self::KEY_INFO.':'.$key;
		return $redis->hGetAll($key);
	}
	
	public function infolist(){
		$redis = by::redis(self::REDIS_PORT);
		$list = $redis->zrevrange(self::KEY_LIST, 0, 100);
		$res = array();
		if(!empty($list) && is_array($list)){
			$cfg = self::getcfg();
			$gnames = fc::getConfig('sname');
			foreach ($list as $v){
				$info = $redis->hgetall($v);
				$msgarr = self::msg_explode($info['msg']);
				if(!is_array($msgarr) || empty($msgarr)){
					echo 'msg decode error'.PHP_EOL;
					
					continue;
				}
				list($type, $gid, $msg) = $msgarr;
				
				$gname = !empty($gnames[$gid]) ? $gnames[$gid] : "gid-{$gid}";
				$desc = !empty($cfg[$type]['desc']) ? $cfg[$type]['desc'] : $type;
				$lasttime = date('Y-m-d H:i:s', $info['time']);
				$res[] = array('gname'=>$gname, 'title'=>$desc, 'msg'=>$msg, 'total'=>$info['total'], 'lasttime'=>$lasttime);
			}
		}
		return $res;
	}
	
	/**
	 * 从预警队列取出并报警
	 * 这个放在内网服务器执行
	 */
	public static function tell($warnings){
		if(empty($warnings)){
			echo 'no warning'.PHP_EOL;
			return;
		}
		echo 'start'.PHP_EOL;
		
		$count = 0;	
		$cfg = self::getcfg();
		$default_reader = self::get_default_reader();
		$gnames = fc::getConfig('gameDir');
		
		foreach($warnings as $warning){
			$msgarr = self::msg_explode($warning);
			if(!is_array($msgarr) || empty($msgarr)){
				echo 'msg decode error'.PHP_EOL;
				continue;
			}
			list($type, $gid, $msg) = $msgarr;
			if(in_array($type, array('bpiderr.txt'))){
				continue;
			}
			$config = $cfg[$type] ? $cfg[$type] : array('reader'=>array());
			if(in_array($type, self::$autotype) || empty($config['reader'])){
				$config['reader'] = array_merge($config['reader'], $default_reader);
			}
			
			
			$gname = !empty($gnames[$gid][3]) ? $gnames[$gid][3] : "gid-{$gid}";
			$no[$gid][$type]++;
			$content = "■ {$msg}".PHP_EOL;
			//$hashkey = self::genhashkey($warning);
			//$content .= " 详情:http://vm.boyaa.com/majiang_php/admin/api.php?do=warning.s_info&k={$hashkey}".PHP_EOL;
			
			//发送给订阅者
			$desc = !empty($config['desc']) ? $config['desc'] : $type;
			$title = "{$gname}-{$desc}";

			//fc::debug("$title: $msg | ".implode(';', $config['reader']), 'warning.txt');
			
			//短信发送
			if(self::_is_sms_time() && $config['sms']){
				$phonemsg = "$title: $msg";
				fc::notice($phonemsg, $config['reader']); //博雅事件系统短信报警接口
			}
			
			//rtx字数有限制，每次发送5条
			$piece = ceil($no[$gid][$type]/5);
			$send[$title]['receiver'] = implode(';', $config['reader']);
			empty($send[$title]['receiver']) && $send[$title]['receiver'] = implode(';', $default_reader);
			//$send[$title]['receiver'] = 'BarryZhu';
			$send[$title]['msg'][$piece]['content'] .= $content;
			$count++;
		}
		
		//rtx发送
		if(!empty($send) && is_array($send)){
			$listapi = fc::getClass('lib', 'listapi');
			foreach ($send as $title=>$v){
				foreach ($v['msg'] as $vv){
					echo "'title'=>$title, receiver:{$v['receiver']}, content:{$vv['content']}".PHP_EOL;
					$listapi->makeRequest( 'Message.Send', array('type'=>'rtx', 'receiver'=>$v['receiver'], 'title'=>$title, 'content'=>$vv['content']));
				}
			}
		}
		echo "count:{$count}".PHP_EOL;
	}

	//是否是发送短信的时间段
	protected static function _is_sms_time(){
		$res = false;
		$dg = date('G');
		$dw = date('w');
		$cfg = self::getcfg();
		if($cfg['warning_sms'] || in_array($dw, array(0, 6))){ //周末，过年或者国庆等重大节日在后台系统报警配置那儿打开warning_sms开关
			if($dg>=8 && $dg<=23){
				$res = true;
			}
		}elseif($dg>=19 && $dg<=23){ //工作日
			$res = true;
		}
		return $res;
	}
	
	public static function msg_implode($type, $msg){
		return json_encode(array($type, GAMEID, $msg));
	}
	
	public static function msg_explode($msg){
		return json_decode($msg);
	}
	
	public static function get_trace($full=1){
		$res = PHP_EOL.'trace:'.PHP_EOL;
		$trace = debug_backtrace();
		//var_dump($trace);
		if($full){
			foreach ( (array)$trace as $k => $v){
				if ($k==0) continue;
				$file = str_replace(ROOTPATH, '', $v['file']);
				$res .= "#{$k} {$v['class']}{$v['type']}{$v['function']} in file:/{$file} on line {$v['line']}".PHP_EOL;
			}
		}else{
			$lasttrace = $trace[3];
			$file = str_replace(ROOTPATH, '', $lasttrace['file']);
			$res .= "#{$k} {$v['class']}{$v['type']}{$v['function']} in file:/{$file} on line {$lasttrace['line']}".PHP_EOL;
		}
		return $res;
	}
	
	public static function genhashkey($warning){
		return substr(md5($warning), 0, 4).substr(md5($warning), -4, 4);
	}
	
	public static function getusername($ids){
		$res = array();
		foreach($ids as $id){
			$user = by::user()->checkUser( array('id'=>$id));
			$res[] = $user['account'];
		}
		return implode(';', $res);
	}
	
	/**
	 * 
	 * @param array $accounts
	 * @return multitype:array
	 */
	public static function getuserbyaccount($accounts){
		$res = array();
		foreach($accounts as $a){
			$user = by::user()->checkUser( array('account'=>$a));
			$res[] = $user;
		}
		return $res;
	}
	
	//手动忽略错误
	public static function check_ignore($msg){
		//报警频率过高，暂时屏蔽，需要请手动查看debug日志文件
		$res = false;
		if(preg_match("/unable to read from socket/i", $msg, $matches)){
			$res = true;
		}
		if(preg_match("/RedisException.*read error on connection/i", $msg, $matches)){
			$res = true;
		}
		
		return $res;
	}
	
	public static function getlogfilename($type){
		$default = "warning.txt";
		$map = array(
				"default" => $default,
				"php" => $default,
				"money" => "hj_setmoney_".date("Ymd"),
				'memcache' => 'memcache.txt',
				'redis' => 'redis.txt',
		);
		return !empty($map[$type]) ? $map[$type] : $default;
	}
	
    public static function get_default_reader(){
		$res = array();
		$phpers = by::user()->get(array('type'=>6, 'status'=>0)); //获取php组成员
		if(!empty($phpers)){
			foreach($phpers as $v){
				$res[] = $v['account'];
			}
			
		}
		return $res;
	}
}