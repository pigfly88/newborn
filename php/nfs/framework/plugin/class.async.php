<?php !defined('BOYAA') AND exit('Access Denied!');

class Async
{
    private static $_redis_conn; //redis对象
    private static $_max_length = 500; //消息最大长度
    private static $_max_size = 10240; //消息队列最大记录数
	private static $_key_subfix = 'ASYNCCALLV5'; //redis存储key后缀
	private static $masterTable = array(
		'#_DB_#.mahjong_marketlog',
		'mahjong.mahjong_marketlog',
		'#_DB_#.market_logs',
		'mahjong.market_logs',
	);

    /**
     * 连接redis
     * @return Lib_Redis
     */
    private static function _connect()
    {
    	if(!is_object(self::$_redis_conn))
    	{
    		self::$_redis_conn = by::redis('core');
    	}
    	
    	return self::$_redis_conn;
    }

	private static function _getKey()
	{
		return GAMENAME . '_' . self::$_key_subfix;
	}

    /**
     * 进入队列
     * @param array $data 数据
     * @return bool 是否成功
     */
	public static function input($data)
	{
		$json = json_encode($data);
		if(!$json || strlen($json) > self::$_max_length)
		{
			fc::debug('#' . __LINE__ .date('Ymd H:i:s'). ':' . $data, __CLASS__);
			return false;
		}

		//判断队列长度，超过指定长度时则不再入列
		if( ($size = self::_connect()->lSize('mahjong_ASYNCCALL')) >= self::$_max_size )//self::_getKey()
		{
			fc::debug('#' . __LINE__ .date('Ymd H:i:s'). ': size=' . $size, __CLASS__);
			return false;
		}
		
		return self::_connect()->rPush('mahjong_ASYNCCALL', $json, false, false);//self::_getKey()
	}

    /**
     * 出队列
     * @param int $num 每次出列个数
	 * @return array 消息数组
     */
	public static function output($num = 10)
	{
		$pop_data = array();
		if(self::_connect()->lSize(self::_getKey()) == 0)
		{
			return $pop_data;
		}
		
		for($i = 0; $i < $num; $i++)
		{
			if($_pop = self::_connect()->lPop(self::_getKey(), false, false))
			{
				$tmp = json_decode($_pop, true);
				if(!$tmp || !is_array($tmp))
				{
					continue;
				}
				$pop_data[] = $tmp;
			}
		}
		
		return $pop_data;
	}
	
    /**
     * 出队列
	 * @return array 消息数组
     */
	public static function output_new(){
		$_pop = self::_connect()->lPop(self::_getKey(), false, false);
		return $_pop === false ? array() : json_decode($_pop, true);
	}

	/**
	 * 异步调用 参数可变 第一个参数为要调用的方法名 之后为要传递的参数(仅支持标量类型)
	 * @return boolean 是否成功
	 */
	public static function call()
	{
		$args = func_get_args();
		
		if(empty($args) || count($args) > 15 || !is_string($args[0]))
		{
			fc::debug('#' . __LINE__ .date('Ymd H:i:s'). ': args=' . var_export($args, true), __CLASS__);
			return false;
		}
		/*
		foreach($args as $arg)
		{
			if(!is_scalar($arg))
			{
				Logs::debug('#' . __LINE__ . ': arg=' . var_export($arg, true), __CLASS__);
				return false;
			}
		}
		*/
		if(!self::input($args))
		{
			fc::debug('#' . __LINE__ .date('Ymd H:i:s'). ': Redis error', __CLASS__);
			return false;
		}
		return true;
	}
	
	/**********************新异步的代码-使用新的key-开始*******************************/
	public static function call_new()
	{
		$args = func_get_args();
		
		if(empty($args) || count($args) > 15 || !is_string($args[0]))
		{
			fc::debug('new#' . __LINE__ .date('Ymd H:i:s'). ': args=' . var_export($args, true), __CLASS__);
			return false;
		}
		if(!self::input_new($args))
		{
			fc::debug('new#' . __LINE__ .date('Ymd H:i:s'). ': Redis error', __CLASS__);
			return false;
		}
		return true;
	}
	public static function input_new($data)
	{
		$json = json_encode($data);
		if(!$json || strlen($json) > self::$_max_length)
		{
			fc::debug('new#' . __LINE__ .date('Ymd H:i:s'). ':' . $data, __CLASS__);
			return false;
		}

		//判断队列长度，超过指定长度时则不再入列
		if( ($size = self::_connect()->lSize(self::_getKey())) >= self::$_max_size )//'mahjong_ASYNCCALL'
		{
			fc::debug('new#' . __LINE__ .date('Ymd H:i:s'). ': size=' . $size, __CLASS__);
			return false;
		}
		
		return self::_connect()->rPush(self::_getKey(), $json, false, false);//'mahjong_ASYNCCALL'
	}
	/**********************新异步的代码-使用新的key-结束*******************************/
	
	
	public static function insertList($table, $fields)
	{
		$table = str_replace('`', '', $table);
		
		self::_connect()->HINCRBY(self::getTableStat(), $table, 1);
		
		return self::_connect()->rpush(self::getTableKey($table), json_encode($fields), false, false);
	}
	
	public static function getList($table){
		$table = str_replace('`', '', $table);
		return self::_connect()->lrange(self::getTableKey($table), 0, -1);
	}
	
	public static function combineInsert($limit = 100)
	{
		$tables = self::_connect()->hgetall(self::getTableStat());
		if(!is_array($tables) || empty($tables)){
			ENVID<2 && self::debug('no tables, key:'.self::getTableStat());
			return false;
		}
		foreach ($tables as $table => $count)
		{
			if($count < $limit)
			{
				ENVID<2 && self::debug('too small');
				continue;
			}
			
			self::_connect()->hdel(self::getTableStat(), $table);
			
			$insert_data = array();
			$filed_array = array();
			$insert_array = array();
			
			while($_pop = self::_connect()->lPop(self::getTableKey($table), false, false))
			{
				$tmp = json_decode($_pop, true);
				if(!$tmp || !is_array($tmp))
				{
					ENVID<2 && self::debug('data err');
					continue;
				}
				
				$insert_data[] = $tmp;
			}
			
			if(!$insert_data)
			{
				continue;
			}

			if(in_array($table, self::$masterTable)){
				$db = by::master();
			} else {
				$db = by::master_log();
			}
			foreach ($insert_data as $fileds)
			{
				if(empty($filed_array))
				{
					foreach ($fileds as $key => $val)
					{
						$filed_array[] = "`" . $key . "`";
					}
				}
				
				$_array = array();
				
				foreach ($fileds as $key => $val)
				{
					$_array[] = "'" . by::master()->escape($val) . "'";
				}
				
				$insert_array[] = "(" . implode(',', $_array) . ")";
				//数据量大的话每100条进行一次插入
				if(count($insert_array) == 100)
				{
					$sql = "INSERT IGNORE INTO " . $table . "(" . implode(',', $filed_array) . ") VALUES " . implode(',', $insert_array);
					$db->query($sql);
					ENVID<2 && self::debug($sql);
					$insert_array = array();
				}
			}
			if(count($insert_array) > 0)
			{
				$sql = "INSERT IGNORE INTO " . $table . "(" . implode(',', $filed_array) . ") VALUES " . implode(',', $insert_array);
				$db->query($sql);
				ENVID<2 && self::debug($sql);
			}
		}
		unset($tables, $insert_data, $filed_array, $insert_array);
	}
	
	public static function getTableStat()
	{
		return GAMENAME . '_in_tbls';	
	}
	
	public static function getTableKey($table)
	{
		return GAMENAME . '_' . $table;	
	}
	
	protected static function debug($param){
		fc::debug($param, 'async.txt');
	}
}