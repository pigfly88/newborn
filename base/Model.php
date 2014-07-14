<?php
class Model extends Component {
	protected $table = '';
    public static $prefix = '';
	protected static $models;
	
	protected function __init(){
		NFS::load(NFS_ROOT.DS.'base'.DS.'DB.php');
		$config = NFS::load(APP_ROOT.DS.'config'.DS.'db.php');

		DB::init($config);
	}
	
	/**
	 * 加载模型
	 *
	 * @param String $model
	 * @return unknown
	 * 
	 */
	public static function load($model){
		$class = $model.MODEL_EXT;
        
        //只实例化一次
		if(is_object(self::$models[$class])){
			return self::$models[$class];
		}
		
		$file = MODEL_ROOT.$model.MODEL_EXT.PHP_EXT;			
		if(NFS::load($file)){
            self::$models[$class] = new $class();
        }else{
            $obj = new Model();
			$obj->table = $model;
			self::$models[$class] = $obj;
        }
        //method_exists($obj, '__init') && $obj->__init();
		return self::$models[$class];
	}
	
	public function getAll($where='', $fields='*'){
		return DB::fetchAll(self::buildSelect($where, $fields), self::buildValues($where));
	}
	
	public function getOne($where, $fields='*'){
		$sql = self::buildSelect($where, $fields).' LIMIT 1';
		return DB::fetch($sql, self::buildValues($where));
	}
	
	public function getColumn($where, $fields='*'){
		$sql = self::buildSelect($where, $fields);
		return DB::fetchColumn($sql, self::buildValues($where));
	}
	
	public function update(){
		
	}
	
	public function delete(){
		
	}
	
	public function add(){
		
	}
	
	public function table(){
		return $this->table ? $this->table : substr($this->classname(), 0, -5);
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
	
	protected function buildValues($where){
		$values = array();
		if(is_array($where) && !empty($where)){
			$values = array_values($where);
		}
		return $values;
	}
}