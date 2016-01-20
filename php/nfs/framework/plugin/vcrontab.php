<?php

/**
 * 虚拟定时任务
 * User: zhikediao
 * Source: Texas Team
 * Date: 15/4/1 0001
 * Time: 16:28
 */
class Cli_Vcrontab {
	const STATISTICS = 0;
	const COMMA = ',';
	const STEP = '/';
	const PERIOD = '-';
	const ANY = '*';

	private $_type = array('i', 'H', 'd', 'm', 'w');
	private $_config = array();//需要执行的任务配置，cfg/deploy/vcrontab/{api}_{envid}.php mjadmin->系统->定时任务 生成即可

	/**
	 * @desc 定时任务
	 * @param void
	 * @return true
	 */
	public function core() {
		if (!$gameid = $_REQUEST['g']) {//站点id
			die('no gameid');
		}
		$nowTime = time();
		$switches = fc::getConfig('vcrontab', 'common', false, false);
		$config = $this->_getConfig();

		$ret = array();
		foreach ($config as $id => $v) {
			$cTime = strtotime($v['endtime']);
			if ($cTime > 0 && $cTime < $nowTime) {
				$ret[$id] = 'time out';
				continue;
			}
			if ($switch = trim($v['switch'])) {//有定义开关
				if (isset($switches[$switch]) && $switches[$switch] != 1) { //没有开启开关
					$ret[$id] = $switch . 'switch off';
					continue;
				}
			}
			if (!$this->check($v['crontab'])) {//非执行点
				$ret[$id] = 'no time';
				continue;
			}
			system("/usr/local/php/bin/php -f " . ROOTPATH . "cli.php 'm=vcrontab&p=exe&g='$gameid'&id='{$id} > /dev/null 2>&1 &"); //子进程
			$ret[$id] = 'do';
		}
		foreach ((array)$ret as $id => $msg) { //记录运行情况
			fc::debug(date('Y/m/d H:i:s') . ",gid:{$gameid},id:{$id},msg:{$msg}", 'vcrontab.log');
		}
		return true;
	}

	/**
	 * @desc 执行
	 * @param Int $id 任务id
	 * @return true
	 */
	public function exe() {
		$gameid = $_REQUEST['g'];
		$id = $_REQUEST['id'];
		$microtime = microtime(true);
		self::STATISTICS && $memoryUsage = memory_get_usage();
		$config = $this->_getConfig($id);
		if (!fc::checkSyntaxBlock($config['code'])) {//语法失败
			$aError = error_get_last();
			fc::debug(date('Y-m-d H:i:s') . " [SYNTAX ERR] CRONTAB ID:{$id}\n" ."#msg:{$aError['message']} #file:{$aError['file']} #line:{$aError['line']} ;", 'vcrontab.err');
			return -300;
		}
		$timelimit = intval($config['timelimit']);
		eval("set_time_limit({$timelimit});register_shutdown_function(array('Cli_Vcrontab','byShutdown'), {$id});" . $config['code']);
		$log = date('Y/m/d H:i:s') . ",gid:{$gameid},id:{$id},msg:ok,time:" . number_format(microtime(true) - $microtime, 4);
		self::STATISTICS && $log .= ",mem:" . number_format(memory_get_usage() - $memoryUsage);
		fc::debug($log, 'vcrontab.log');
		return true;
	}

	/**
	 * @desc 核验
	 * @param String $crontab 5段（分时日月周）连接字符串
	 * @param String $split 分隔符,默认#
	 * @return Boolean/Int
	 */
	public function check($crontab) {
		if (empty($crontab) || !is_array($crontab)) {
			return -100;
		}

		$cRet = array();
		foreach ($this->_type as $type) {
			if(!isset($crontab[$type])) { //定时规则不完整
				return -101;
			}
			$cRet[$type] = $this->_analy($crontab[$type], $type);
			if ($cRet[$type] < 0) {
				return false;
			}
		}
		$ret = $cRet['d'] && $cRet['m'] && $cRet['w'];
		if ((is_bool($cRet['d']) || is_bool($cRet['m'])) && is_bool($cRet['w'])) {//如果都定义了，则（日月）（周）一对为true即可
			$ret = ($cRet['d'] && $cRet['m']) || $cRet['w'] ? true : $ret;
		}
		return $cRet['i'] && $cRet['H'] && $ret ? true : false;
	}

	/**
	 * @desc 单段分析
	 * @param String $string 单段
	 * @param String $type 分i 时H 日d 月m 周w
	 * @return Boolean/Int true通过
	 */
	private function _analy($string, $type) {
		if (!strlen($string) || !is_string($string)) {
			return -200;
		}
		if (!in_array($type, $this->_type, true)) {
			return -201;
		}

		$step = 0;
		$isANY = false;
		$validTimeBox = $commaTimeBox = array();//有效时间

		if (strpos($string, self::COMMA) !== false) {//有‘,’号
			$commaTimeBox = explode(',', $string);
		} else {
			$commaTimeBox[] = $string;
		}

		foreach ((array)$commaTimeBox as $v) {
			$step = 0;

			if (strpos($v, self::STEP) !== false) {//有‘/’号
				list($v, $step) = explode(self::STEP, $v);
				$step = intval($step);
			}

			if ($v === self::ANY) {//有*号
				$isANY = true;
				break;
			}

			$tmp = $step ? $step : 1;
			if (strpos($v, self::PERIOD) !== false) {//有‘-’号
				list($start, $end) = explode(self::PERIOD, $v);
				$min = min(array((int)$start, (int)$end));
				$max = max(array((int)$start, (int)$end));
				for ($min; $min <= $max; $min += $tmp) {
					$validTimeBox[] = (int)$min;
				}
			} else {
				$validTimeBox[] = (int)$v;
			}
		}

		$nowTime = (int)date($type);
		if ($isANY === true) {//有‘*’号
			if (!$step) return 1;
			return $nowTime % $step == 0 ? true : false;
		} else {
			$validTimeBox = array_unique($validTimeBox);
			return in_array($nowTime, $validTimeBox, true) ? true : false;
		}
		return false;
	}

	/**
	 * @desc 获取配置
	 * @param Int $id 配置ID
	 * @return Array
	 */
	private function _getConfig($id = 0) {
		if (empty($this->_config)) {
			$this->_config = fc::getDeploy('vcrontab');
		}
		return empty($id) ? (array)$this->_config : (array)$this->_config[$id];
	}

	/**
	 * 子进程异常
	 * @param $id
	 * @return bool
	 */
	static function byShutdown($id) {
		$aError = error_get_last();
		if (!(is_array($aError) && in_array($aError['type'], array(E_ERROR, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR)))) {
			return false;
		}
		fc::debug(date('Y-m-d H:i:s') . " [SHUTDOWN] CRONTAB ID:{$id}\n" . "#msg:{$aError['message']} #file:{$aError['file']} #line:{$aError['line']} ;", 'vcrontab.err');
		return true;
	}
}