<?php

/**
 * 常用方法类
 * @author OuyangLiu
 */
class fc {

	static $autoDir = array('api', 'cli', 'lib', 'model', 'cmsapi');
	static $defaultAutoDir = 'lib';
	static $specialCfg = array('inc', 'system', 'activityurl'); //如果还有需要环境的配置文件，请加到这里，但建议不要太多

	/**
	 * 获取文件地址
	 * @param string $dir 目录如：api model 等
	 * @param string $fileName 文件名
	 * @return string
	 */
	public static function getFilePath($dir, $fileName) {
		$dir = strtolower($dir);
		$fileName = strtolower($fileName);
		if (strstr($fileName, 'my_') !== false) { //加载子类
			return ROOTPATH . GAMEDIR . $dir . '/' . $fileName . '.php';
		}
		//加载父类
		return ROOTPATH . $dir . '/' . $fileName . '.php';
	}

	/**
	 * 获取配置文件
	 * @param string $key 支持多维数据配置，如：$config['member']['vip'] 可用$key = 'member.vip'获取
	 * @param string $name 指定要获取的配置文件，不要带前缀
	 * @param string $checkChange 是否检测配置文件是否是最新的，如果不是最新则强制重新加载（一般用于执行时间很长的脚本中，如死循环等）
	 * @param boolean $die 找不到的情况下是否直接die掉
	 * @return array
	 */
	public static function getConfig($key, $name = 'common', $checkChange=false, $die=true) {
		static $_config = array();
		static $_ltime = 0;
		$thisConfig = null;
		$key = str_replace(".", "']['", $key);
		eval("\$thisConfig = \$_config['$key'];");
		if($checkChange){
			$mtime = fc::getConfigMtime($name);
			if($mtime != $_ltime){
				$_ltime = $mtime;
				$thisConfig = null;
			}
		}
		if (is_null($thisConfig)) {
			if (in_array($name, self::$specialCfg) && (ENVID !== 3)) $name .= '_' . ENVID; //测试环境加后缀，正式的不加
			$aFile[] = self::getFilePath('config', $name);
			$aFile[] = self::getFilePath('config', "my_{$name}");
			if (SVRID > 0) $aFile[] = self::getFilePath('config/svr_' . SVRID, "my_{$name}");
			foreach ($aFile as $file) {
//fc::debug($file,'jungle20151124');
				if (is_file($file)) include $file;
			}
			is_array($config) && $_config = array_merge($_config, $config);
			eval("\$thisConfig = \$_config['$key'];");
			if ($die && is_null($thisConfig)) {
				$files = str_replace(ROOTPATH, '', implode(' & ', $aFile));
				$errStr = "\$config['$key'] not found in file: {$files}";
				fc::debug($errStr, 'phperr.txt');
				die($errStr);
			}
		}
		return $thisConfig;
	}

	/**
	 * 加载整个配置文件
	 * @param string $name 指定要获取的配置文件，不要带前缀
	 * @return array
	 */
	public static function includeConfig($name = 'common') {
		$config = array();
		if (in_array($name, self::$specialCfg) && (ENVID !== 3)) $name .= '_' . ENVID; //测试环境加后缀，正式的不加
		$aFile[] = self::getFilePath('config', $name);
		$aFile[] = self::getFilePath('config', "my_{$name}");
		if (SVRID > 0) $aFile[] = self::getFilePath('config/svr_' . SVRID, "my_{$name}");
		foreach ($aFile as $file) {
			if (is_file($file)) include $file;
		}
		return $config;
	}

	/**
	 * 获取所有游戏的通用功能配置信息
	 * @param string $deploy
	 * @param bool $checkChange 是否检测配置文件是否是最新的，如果不是最新则强制重新加载（一般用于执行时间很长的脚本中，如死循环等）
	 * @return array
	 */
	public static function getAllDeploy($deploy, $checkChange = false) {
		static $_cache = array();
		static $_ltime = array();
		$gameDirCfg = fc::getConfig('gameDir');
		foreach ($gameDirCfg as $k => $v) {
			if (!$gameDir = strval($v[0])) continue;
			$apiEnvid = "0_" . ENVID;
			$gemePath = ROOTPATH . $gameDir . "cfg/";
			$file = $gemePath . "deploy/{$deploy}/{$apiEnvid}.php";
			if ($checkChange && is_readable($file)) {
				$mtime = self::getDeployMtime($deploy, 0, 0, $gemePath);
				if ($mtime != intval($_ltime[$deploy][$gameDir])) {
					$_ltime[$deploy][$gameDir] = $mtime;
					unset($_cache[$deploy][$gameDir]);
				}
			}
			if (!isset($_cache[$deploy][$gameDir])) {
				$_cache[$deploy][$gameDir] = is_readable($file) ? include $file : array();
			}
		}
		$ret = array();
		foreach ($_cache[$deploy] as $value) {
			if (empty($value)) continue;
			foreach ($value as $k => $v) {
				$ret[$k] = $v;
			}
		}
		return $ret;
	}

	/**
	 * 获取通用功能配置信息
	 * @param string $deploy
	 * @param int $api 如果配置不区分api可不传，api=base则获取根目录下的配置
	 * @param bool $checkChange 是否检测配置文件是否是最新的，如果不是最新则强制重新加载（一般用于执行时间很长的脚本中，如死循环等）
	 * @return array
	 */
	public static function getDeploy($deploy, $api = 0, $checkChange = false) {
		static $_cache = array();
		static $_ltime = array();
		if($api === 'base') {
			$base = 1;
			$api = 0;
			$apiEnvid = "{$api}_" . ENVID;
			$file = PATH_COM_CFGC . "deploy/{$deploy}/{$apiEnvid}.php";
		} else {
			$base = 0;
			$apiEnvid = "{$api}_" . ENVID;
			$file = PATH_GAME_CFGC . "deploy/{$deploy}/{$apiEnvid}.php";
		}
		if ($checkChange && is_readable($file)) {
			$mtime = self::getDeployMtime($deploy, $api, $base);
			if ($mtime != intval($_ltime[$apiEnvid][$deploy])) {
				$_ltime[$apiEnvid][$deploy] = $mtime;
				unset($_cache[$apiEnvid][$deploy]);
			}
		}
		if (isset($_cache[$apiEnvid][$deploy])) {
			return $_cache[$apiEnvid][$deploy];
		}
		$_cache[$apiEnvid][$deploy] = is_readable($file) ? include $file : array();
		return $_cache[$apiEnvid][$deploy];
	}
	
	/**
	 * 获取通用功能配置文件最后时间
	 * @param string $deploy
	 * @param int $api 如果配置不区分api可不传
	 * @param int $base 是否基础配置
	 * @param bool $path 指定路径
	 * @return int
	 */
	public static function getDeployMtime($deploy, $api = 0, $base = 0, $path = false) {
		$api = "{$api}_" . ENVID;
		$file = $base == 1 ? PATH_COM_CFGC . "deploy/{$deploy}/{$api}.php" : ($path === false ? PATH_GAME_CFGC : $path) . "deploy/{$deploy}/{$api}.php";
		if(is_readable($file)) {
			clearstatcache();
			return (int)filemtime($file);
		}
		return 0;
	}

	/**
	 * 获取配置文件更新时间
	 * @param string $name 指定要获取的配置文件，不要带前缀
	 * @return int
	 */
	public static function getConfigMtime($name = 'common') {
		clearstatcache();
		$mtime = 0;
		if (in_array($name, self::$specialCfg) && (ENVID !== 3)) $name .= '_' . ENVID; //测试环境加后缀，正式的不加
		$aFile[] = self::getFilePath('config', $name);
		$aFile[] = self::getFilePath('config', "my_{$name}");
		if (SVRID > 0) $aFile[] = self::getFilePath('config/svr_' . SVRID, "my_{$name}");
		foreach ($aFile as $file) {
			if (is_file($file)) $mtime = max($mtime, (int)filemtime($file));
		}
		return $mtime;
	}

	/**
	 * 获取语言文件
	 * @param string $key 支持多维数据配置，如：$lang['member']['desc'] 可用$key = 'member.desc'获取
	 * @return array
	 */
	public static function getLang($key) {
		static $_lang = array();
		$thisLang = null;
		$key = str_replace(".", "']['", $key);
		eval("\$thisLang = \$_lang['$key'];");
		if (is_null($thisLang)) {
			$aFile[] = self::getFilePath('lang', LANGFILE);
			$aFile[] = self::getFilePath('lang', "my_" . LANGFILE);
			if (SVRID > 0) $aFile[] = self::getFilePath('lang/svr_' . SVRID, "my_" . LANGFILE);
			foreach ($aFile as $file) {
				if (is_file($file)) include $file;
			}
			is_array($lang) && $_lang = array_merge($_lang, $lang);
			eval("\$thisLang = \$_lang['$key'];");
			if (is_null($thisLang)) {
				$errStr = "\$lang['$key'] not found in file: " . implode(' & ', $aFile);
				fc::debug(date('Ymd H:i:s') . " => {$errStr}", 'getLang.err.txt');
				die($errStr);
			}
		}
		return $thisLang;
	}

	/**
	 * 获取类（优先返回子类，如果没有则返回父类）
	 * @param string $dir
	 * @param string $name 类名，不要带前缀
	 * @param $param new类时要的参数
	 */
	public static function getClass($dir, $name, $param = null, $param1 = null) {
		$dir = strtolower($dir);
		$name = strtolower($name);
		if(empty($dir) || empty($name)){
			die("class err!");
		}
		if ($dir == self::$defaultAutoDir) { //默认目录lib里无子类，且类名不加前缀，不大写
			if (class_exists($name)) $className = $name;
		} else {
			$dir = ucfirst($dir);
			$name = ucfirst($name);
			if (class_exists($dir . '_My_' . $name)) { //加载子类
				$className = $dir . '_My_' . $name;
			} elseif (class_exists($dir . '_' . $name)) { //加载父类
				$className = $dir . '_' . $name;
			}
		}
		if(!$className){
			$dir!='api' && fc::debug("#class err - {$dir}/{$name}", 'phperr.txt');
			die("$name class err!");
		}
		
		return new $className($param, $param1);
	}

	/**
	 * 自动加载
	 * @param string $className
	 */
	public static function autoLoad($className) {
		if (empty($className) || !is_string($className)) return false;
		list($dir, $fileName) = explode('_', $className, 2);
		$dir = strtolower($dir);
		if (!in_array($dir, self::$autoDir)) { //默认加载lib里的文件
			$dir = self::$defaultAutoDir;
			$fileName = "class.{$className}";
		}
		$file = self::getFilePath($dir, $fileName);
		if (empty($file) || !is_file($file)) return false;
		include $file;
		return true;
	}

	/**
	 * 处理返回数据
	 * @param $data 数据
	 * @param $type 返回的格式
	 */
	public static function response($data, $type = 1) {
		switch ($type) {
			case 1:
				$data = json_encode($data);
				break;
			case 2:
				echo json_encode($data);
				exit;
		}
		return $data;
	}

	/**
	 * @desc  统一输出接口
	 * @param type $status 返回状态
	 * @param type $msg 返回信息
	 * @param type $data 返回数据
	 * @param type $exit 是否终止
	 * @param type $only_data true:返回的数据格式不按照status/msg/data的形式走，直接返回$data原数据
	 *
	 */
	public static function json_response($status = 1, $msg = null, $data = null, $exit = true, $only_data = false) {
		if (!$only_data) {
			$array = array(
				'status' => $status,
			);

			if ($msg) {
				$array = array_merge($array, array('msg' => $msg));
			}

			if ($data) {
				$array = array_merge($array, array('data' => $data));
			}
		} else {
			$array = $data;
		}

		if (Helper::checkHtml5($_GET)) {
			if (isset($_GET['callback'])) {
				$callback = filter_var($_GET['callback'], FILTER_SANITIZE_STRING);
			} else {
				$callback = filter_var('jQuery_html5_mahjong_error', FILTER_SANITIZE_STRING);
			}
			echo $callback . '(' . json_encode($array) . ');';
		} else {
			// 返回数据进行压缩处理，目前支持base64和zlib两种处理
			$returnStr = json_encode($array);
			$ioFilters = isset($_SERVER['HTTP_IO_FILTERS']) && !empty($_SERVER['HTTP_IO_FILTERS'])
						 ? trim($_SERVER['HTTP_IO_FILTERS']) : '';
			if ($ioFilters == 'base64')
			{
				header("io-filters: {$_SERVER['HTTP_IO_FILTERS']}");
				$endStr = base64_encode($returnStr);
				echo $endStr;
			}
			else if($ioFilters == 'zlib')
			{
				header("io-filters: {$_SERVER['HTTP_IO_FILTERS']}");
				$endStr = gzcompress($returnStr);
				echo $endStr;
			}
			else
			{
				echo $returnStr;
			}
		}

		if ($exit) {
			exit;
		} else if (function_exists('fastcgi_finish_request')) {
			fastcgi_finish_request();
		}
	}


	/**
	 * 写日志
	 * @param unknown_type $params
	 * @param string $file 支持多级目录如: mylog/log.txt，程序会自动写入/data/wwwroot/majiang_v5/data/mylog/log.txt
	 * @param int $fsize
	 */
	public static function debug($params, $filename = 'debug.logs.txt', $fsize = 2) {
		clearstatcache();
		$gameName = (!defined('GAMEID') || GAMEID == 5) ? '' : GAMEDIR; //不同游戏日志到相应目录
		$file = PATH_COM_DAT. $gameName . $filename . '.php';
		$dir = dirname($file);
		is_dir($dir) or mkdir($dir, 0777, true);
		$size = file_exists($file) ? @filesize($file) : 0;
		$flag = $size < max(1, $fsize) * 1024 * 1024; //标志是否附加文件.文件控制在1M大小
		$prefix = $size && $flag ? "" : "<?php (isset(\$_GET['p']) && (md5('&%$#'.\$_GET['p'].'**^')==='d4ec29122b5914374e782c3dc3c307aa')) or die();?>\n"; //有文件内容并且非附加写
		is_scalar($params) or ($params = var_export($params, true)); //是简单数据
		$res = @file_put_contents($file, $prefix . date('Y-m-d H:i:s')." ". $params . "\n", $flag ? FILE_APPEND : null);
		warning::add($params, $filename); //加入预警队列
		return $res;
	}

	/**
	 * 写错误日志
	 * @param  $params
	 */
	public static function writeErrLogs($type = '', $message = '', $file = '', $line = '', $context = '') {
		$type or extract((array)error_get_last(), EXTR_OVERWRITE, '');
		$type && ($type !== E_NOTICE) && self::debug(date('Y-m-d H:i:s') . " # " . $message . " # file:" . $file . " # line:" . $line . ";", 'errLogs.txt');
		return true;
	}

	//获取微秒时间
	public static function microtime_float() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	/**
	 * session 统一加到这里，以后方便设置
	 */
	public static function session_start() {
		session_start();
	}

	/**
	 * 去反斜线
	 * @param string or array $value
	 */
	public static function stripslashes_deep($value) {
		$value = is_array($value) ? array_map('fc::stripslashes_deep', $value) : stripslashes($value);
		return $value;
	}

	/**
	 * 防止并发操作
	 * @param int $mid
	 * @return bool
	 */
	public static function concurrent($file, $func, $isClear = false) {
		if (empty(init::$params['mid'])) return false;
		// 不处理的请求,后续提取对应的配置出来
		$exemptConf = array(
			// 麻将类型 => m(key) => p(array)
			10 => array(
				"pay"=>array('payconf'),
				"dailyranking"=>array('getEXPList', 'getMoneyList', 'getPTTodayList'),
				"exchange"=>array('exchangeList'),
				"honour"=>array('getHonourInfo')
			),
			11=> array(
				"pay"=>array('payconf')
			),
			12=> array(
				"pay"=>array('getconf')
			)
		);
		if (isset($exemptConf[GAMEID][$file]) && in_array($func, $exemptConf[GAMEID][$file])) {
			return false;
		}
		$bfKey = Model_Keys::getConcurrentKey($file, $func, init::$params['mid']);
		if ($isClear) {
			by::cache('core')->delete($bfKey);
		} elseif (!by::cache('core')->add($bfKey, 1, 1, false)) { //并发操作
			if ($file == 'market' && $func == 'mypacket') {
				fc::json_response(0, null, '不要这么急啦!', true, true);
			} elseif ($file == 'qiandao' && in_array($func, array('getSignReward', 'getGiftReward', 'retroActive', 'getSign'))) {
				$info['time'] = time();
				fc::json_response(0, '不要这么急啦!', array('info' => $info));
			} elseif ($file == 'bankrupt' && $func == 'mobilebankrupt') {
				fc::json_response(0, '一大波金币正在路上，请点击领取!');
			}
			fc::json_response(0, '不要这么急啦!');
		}
		return true;
	}

	/**
	 * 平台验证
	 * @param string $name
	 * @param string $appid
	 * @param string $appkey
	 */
	public static function platform($name, $appid = '', $appkey = '') {
		static $_p = array();
		$class = ucfirst($name);
		if (!is_object($_p[$class])) {
			include_once PATH_COM_LIB . 'platform/class.' . strtolower($name) . '.php';
			$_p[$class] = new $class($appid, $appkey);
		}
		return $_p[$class];
	}

	/**
	 * 检查某一段PHP代码是否有语法错误
	 * @param String $string 代码片段
	 * @return Boolean
	 */
	public static function checkSyntaxBlock( $string ){
		if( !$string ){
			return false;
		}
		return @eval( 'return true;' . $string ) ? true : false;
	}

	/**
	 * 把对应的值压入数组.此处过滤掉null及空串以节约存储
	 */
	public static function combine($aKey, $aValue) {
		foreach ((array)$aKey as $key => $value) {
			($aValue[$value] !== null) && ($aValue[$value] !== '') && ($aTemp[$key] = $aValue[$value]);
		}
		return (array)$aTemp;
	}

	/**
	 * 反转数组
	 */
	public static function uncombine($aKey, $aValue) {
		foreach ((array)$aKey as $key => $value) {
			$aTemp[$value] = isset($aValue[$key]) ? $aValue[$key] : '';
		}
		return (array)$aTemp;
	}
	
	//博雅事件接口，用来发报警短信
	public static function notice($msg, $receiver){
		$status = false;
		if($msg=='' || empty($receiver) || !is_array($receiver)){
			return false;
		}
		$contact_user = implode(',', $receiver);
		$request_data = array (
				'auth_id'       => 17,
				'auth_token'    => '964f28d7ccc4015a0d3e1ae2f0d15f96',
				'content'       => $msg,
				'fbpid'         => '',
				'priority'      => 5,
				'contact_user'  => $contact_user,
				'time'          => time()
		);
		$request_url = 'http://notice.boyaa.com/index.php/common_api/app_data?'.http_build_query($request_data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $request_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$document = curl_exec($ch);
		if(!empty($document)){
			$res = json_decode($document, true);
			if($res['error_code']==0){
				$status = true;
			}
		}
		return $status;
	}

}