<?php
class Model extends Component {
    protected static $db = null;
	protected $table = '';
	public $columns = null;
    public $prefix = '';
	protected static $models;
	
	protected function __init(){
		
	}
	protected function db(){
        if(!is_object(self::$db)){
            NFS::load(NFS_BASE_ROOT.'DB.php');
            $config = NFS::load(APP_ROOT.DS.CONFIG_FOLDER_NAME.DS.'db.php');
            self::$db = DB::init($config);
        }
        return self::$db;
	}
	
	/**
	 * 加载模型
	 *
	 * @param String $model
	 * @return unknown
	 * 
	 */
	public static function load($model){
        self::db();
		$class = $model.MODEL_EXT;
        
        //只实例化一次
		if(is_object(self::$models[$class])){
			return self::$models[$class];
		}
		
		$file = MODEL_ROOT.$model.MODEL_EXT.PHP_EXT;
        
		if(NFS::load($file)){
            self::$models[$class] = new $class();
        }else{//自动实例化不存在的model
            $obj = new Model();
			$obj->table = $model;
			self::$models[$class] = $obj;
        }
        self::$models[$class]->table = $model;
        //表结构
        self::$models[$class]->columns = self::columns($model);
        //var_dump($obj->columns);
        method_exists($obj, '__init') && $obj->__init();
		return self::$models[$class];
	}
	
	/**
	 * 执行一条sql语句，增删改
	 *
	 * @param string $sql
	 * @param array/string $param
	 * @return boolean
	 */
	public function execute($sql, $param=null){
		return DB::execute($sql, $param);
	}
	
	/**
	 * 执行一条查询
	 *
	 * @param string $sql
	 * @param array/string $param
	 * @return array
	 */
	public function query($sql, $param=null){
		return DB::fetchAll($sql, $param);
	}
	
	
	public function fetchAll($where='', $fields='*'){
		return DB::fetchAll(self::buildSelect($where, $fields), self::buildValues($where));
	}
	
	public function fetchOne($where, $fields='*'){
		$sql = self::buildSelect($where, $fields).' LIMIT 1';
		return DB::fetch($sql, self::buildValues($where));
	}
	
	public function fetchColumn($where, $fields='*'){
		$sql = self::buildSelect($where, $fields);
		return DB::fetchColumn($sql, self::buildValues($where));
	}
	
	public function table(){
		return $this->table ? $this->prefix.$this->table : $this->prefix.substr($this->classname(), 0, -5);
	}
	
	public function sql(){
		
		
	}
	
	public function where(){
		
	}
	
	public function select(){
		
	}
	
	public function orderby(){
		
	}
	
	public function limit(){
		
	}
	
	public function select(){
		return DB::execute(self::buildUpdate($data, $table));
	}
	
	public function update(){
		return DB::execute(self::buildUpdate($data, $table));
	}
	
	public function delete(){
		return DB::execute(self::buildDelete($data, $table));
	}
	
	public function insert($data, $table=''){
		return DB::execute(self::buildInsert($data, $table));
	}
	
	protected function buildWhere($where){
		$keys = ' 1=1 ';
		if(is_array($where) && !empty($where)){
			foreach ($where as $k=>$v){
				$keys .= " and $k=?";
			}
		}
		return $keys;
	}
	
	protected function buildSelect($where, $fields){
		$where = self::buildWhere($where);
		$table = self::table();
		if(is_array($fields) && !empty($fields)){
			$fields = implode(', ',$fields);
		}
		return "SELECT {$fields} FROM {$table} WHERE {$where}";
	}
	
	protected function buildInsert($data, $table=''){
		$table = empty($table) ? self::table() : $this->prefix.$table;
		
		$sql = "INSERT INTO {$table} (";
		if(!is_array($data) || empty($data)) exit('buildInsert fail,data is empty');
		
		$sql .= implode(', ',array_keys($data));
		$sql .= ") VALUES (";
		//IS_NULLABLE, DATA_TYPE, COLUMN_DEFAULT
		foreach ($data as $v){
			$sql .= "'{$v}',";
		}
		$sql = rtrim($sql, ',');
		$sql .= ");";
		//echo $sql;
		return $sql;
	}
	
	public function buildDelete(){
		
	}
	
	public function buildUpdate(){
		
	}
	
	protected function buildValues($where){
		$values = array();
		if(is_array($where) && !empty($where)){
			$values = array_values($where);
		}
		return $values;
	}
	
	public function columns($table){
		$sql="SELECT * FROM information_schema.columns where table_schema='nfs' and table_name='{$table}' order by COLUMN_NAME";
		$res = DB::fetchAll($sql);
		if(!is_array($res) || empty($res)){
			//log
		}
		return json_decode(json_encode($res), true);
	}
}