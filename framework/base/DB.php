<?php
class db{
	static $current_driver;
	static $default_driver;

	public static function driver($driver=null, $default=0){
		if($default){
			if(is_null($driver)){
				return self::$default_dirver;
			}else{
				if(is_object(self::$default_driver))	return self::$default_driver; //默认的数据库只设置一次
				self::$default_driver = dbfactory::driver($driver);
				self::$current_driver = self::$default_driver;
				return self::$default_driver;
			}
		}else{
			return is_null($driver) ? self::$current_driver : self::$current_driver = dbfactory::driver($driver);
		}
	}
	
	public function __callstatic($name, $params){
		return call_user_func_array(array(self::$default_driver, $name), $params);
	}

}

class dbconfig{
	public static function get($driver){
		return oo::cfg('db.'.$driver);
	}
}

class dbfactory{
	static $drivers;
	const DRIVER_TAIL = '_driver';

	public static function driver($driver){
		$config = dbconfig::get($driver);
		list($driver_type, $desc) = explode('.', $driver);
		$driver_type .= self::DRIVER_TAIL;
		if(isset(self::$drivers[$driver])){
			return self::$drivers[$driver];
		}
		if(class_exists($driver_type)){
			return self::$drivers[$driver] = new $driver_type($config);
		}else{
			die('driver not found'.PHP_EOL);
		}
	}
}

/**
 * driver的模板
 * 所有的drive必须定义这里包含的方法
 */
interface dbdriver_template{
	//public function conn();

	public function get($sql, $param=null);

	public function getall($sql, $param=null, $fetchStyle);

	public function delete();

	public function update();
}

/**
 * driver的公共方法
 * 可以共用的方法写在这里
 */
class dbdriver{
	public function debug($msg){
		echo $msg.PHP_EOL;
	}

	public function delete(){

	}

	public function update(){

	}
}

/**
 * pdo驱动
 *
 */
class pdo_driver extends dbdriver implements dbdriver_template{
	protected $db;
	protected $table;
	
	public function __construct($config){
		try{
		    $this->db = new PDO($config['dsn'], $config['username'], $config['password'],
		    array(
			    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'",
			    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,//ERRMODE_WARNING, PDO::ERRMODE_EXCEPTION, PDO::ERRMODE_SILENT
			    PDO::ATTR_TIMEOUT => 10,
		    ));
		    
		    //关闭本地模拟prepare
		    $this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}catch (PDOException $e){
		    die('Connection failed: ' . $e->getMessage());
		}
	}

	public function table($table){
		$this->table = $table;
	}
	
	protected function statement($sql, $param=null){
		try{
			$stmt = $this->db->prepare($sql);
		}catch (PDOException $e){
			echo $sql;
			die('prepare failed: ' . $e->getMessage());
		}

		if(false===$stmt)	{
			echo "\nPDO::errorInfo():\n";
    		p($this->db->errorInfo());
		}
		if(!is_null($param)){
			$i=1;
			if(is_array($param) && !empty($param)){
				foreach ($param as $v){
					$stmt->bindParam($i++, $v);
				}
			}else{
				$stmt->bindParam($i, $param);
			}
		}
		return $stmt;	
	}
	
	/**
	 * 
	 * @param unknown $sql
	 * @param string $param
	 * @return boolean
	 * @usage db::execute("insert into tbl_user values (null, 'kkoo')"); 
	 *
	 */
	public function execute($sql, $param=null){
		$stmt = $this->statement($sql, $param);
		return $stmt->execute();
	}
	
	/**
	 * @usage db::get("select * from tbl_user");
	 * @see dbdriver_template::get()
	 */
	public function get($sql, $param=null, $fetchStyle=PDO::FETCH_ASSOC){
		$stmt = $this->statement($sql, $param);		
		$stmt->execute();
		return $stmt->fetch($fetchStyle);
		
	}
	
	/**
	 * @usage db::getall("select * from tbl_user");
	 * @see dbdriver_template::getall()
	 */
	public function getall($sql, $param=null, $fetchStyle=PDO::FETCH_ASSOC){
		$stmt = $this->statement($sql, $param);		
		$stmt->execute();
		return $stmt->fetchAll($fetchStyle);
	}
	
	public function insert($table, $data){
		
	}
	/*
	public function getColumn($sql, $param=null){
		$stmt = $this->statement($sql, $param);
		
		if($stmt->execute()){
			return $stmt->fetchColumn();
		}
	}
	*/

}