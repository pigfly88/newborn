<?php
class DB{
	protected static $db = null;
	
	public static function connect($config){
		is_null(self::$db) && self::$db = new PDO($config['dsn'], $config['username'], $config['password']);
		return self::$db;
	}
	
	protected static function statement($sql, $param=null){
		$stmt = self::$db->prepare($sql);
		
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
	
	public static function fetchAll($sql, $param=null){
		$stmt = self::statement($sql, $param);
		
		if($stmt->execute()){
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
	}
	
}