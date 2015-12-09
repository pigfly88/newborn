<?php
//简单工厂模式

//db模板，作用：接口统一，有利于规范
interface db_interface{
	public function query($sql);
}

//mysql类
class mysql implements db_interface{
	public function __construct($opt){
		echo "connect to mysql: {$opt['host']} {$opt['username']} {$opt['password']}<br />";
		$this->db = $opt; //模拟mysql连接
	}
	
	public function query($sql){
		echo "[mysql] {$sql}<br />"; //模拟查询
	}
}

//oracle类
class oracle implements db_interface{
	public function __construct($opt){
		echo "connect to oracle: {$opt['host']} {$opt['username']} {$opt['password']}<br />";
		$this->db = $opt; //模拟oracle连接
	}
	
	public function query($sql){
		echo "[oracle] {$sql}<br />";
	}
}

//创建具体db的工厂
class dbfactory{
	static $obj;
	
	public static function create($name, $opt){
		if(!isset(self::$obj[$name])){
			$dbtype = $opt['type'];
			self::$obj[$name] = new $dbtype($opt);
		}
		return self::$obj[$name];
	}
}

//对外使用的接口
class db{
	protected static $db_default;
	protected static $cfg;
	
	public static function init($cfg){
		self::$cfg = $cfg;
		isset($cfg['default']) && self::$db_default = $cfg['default']['type'];
	}
	
	public static function selectdb($name){
		return dbfactory::create($name, self::$cfg[$name]);
	}
	
	public static function __callStatic($name, $args){
		self::selectdb('default');
		call_user_func_array(array(self::$db_default, $name), $args);
	}
}

$cfg = array(
	'default'=>array('type'=>'mysql', 'host'=>'192.168.200.10', 'username'=>'root', 'password'=>'123456'),
	'db1'=>array('type'=>'mysql', 'host'=>'192.168.200.20', 'username'=>'root', 'password'=>'123456'),
	'db2'=>array('type'=>'oracle', 'host'=>'192.168.200.30', 'username'=>'root', 'password'=>'123456'),
);

db::init($cfg);

db::query('select * from user');
db::query('select * from role');
db::query('select * from test');

db::selectdb('db1')->query('select * from user');
db::selectdb('db1')->query('select * from admin');

db::selectdb('db2')->query('select * from bank');