<?php
/**
 * 数据库操作类
 *
 */
class DB{
	protected static $dbList = array();
	protected static $db = null;
	
	public static function connect($config, $once=true){
		if(empty($config) || count($config)<1)	exit('db config is empty');
		
		$init = isset($config[0]) ? $config[mt_rand(0, count($config)-1)] : $config;

		if(!$init['dsn'])	throw new NFSException('db config parse error');
		
		if(!isset(self::$dbList[$init['dsn']])){	
			try{
				$charset = self::getCharset($init['dsn']);
			    $db = new PDO($init['dsn'], $init['username'], $init['password'],array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '{$charset}'"));
			    
			    //关闭本地模拟prepare
			    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
			    
			    self::$db = $db;
			    self::$dbList[$init['dsn']] = $db;
			}catch (PDOException $e){
			    echo 'Connection failed: ' . $e->getMessage();
			}
		}else{
			self::$db = self::$dbList[$init['dsn']];
		}
		return self::$dbList[$init['dsn']];
	}
	
	protected static function getCharset($dsn){
		$dsninfo = explode(';', $dsn);	
		$cv='';
		foreach($dsninfo as $v){
			if(false!==strpos(strtolower($v), 'charset')){
				list($ck, $cv) = explode('=', $v);
				break;
			}
		}
		return empty($cv) ? 'utf8' : $cv;
	}
	
	protected static function statement($sql, $param=null){
		$stmt = self::$db->prepare($sql);		
		if(!$stmt)	{
			throw new NFSException('stmt false');
		}
		
		if(!is_null($param)){
			if(is_array($param) && !empty($param)){
				$i=1;
				foreach ($param as $v){
					$stmt->bindParam($i++, $v);
				}
			}else{
				$stmt->bindParam(1, $v);
			}
		}
		return $stmt;
	}
	
	public static function fetch($sql, $param=null){
		$stmt = self::statement($sql, $param);
		
		if($stmt->execute()){
			return $stmt->fetch(PDO::FETCH_ASSOC);
		}
	}
	
	public static function fetchAll($sql, $param=null, $fetchStyle=PDO::FETCH_ASSOC, $fetch_argument){
		$stmt = self::statement($sql, $param);
		
		if($stmt->execute()){
			return $stmt->fetchAll($fetchStyle);
		}
	}
	
	public static function fetchColumn($sql, $param=null){
		$stmt = self::statement($sql, $param);
		
		if($stmt->execute()){
			return $stmt->fetchColumn();
		}
	}
	
}