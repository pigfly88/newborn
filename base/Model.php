<?php
class Model extends Component {
	protected $table='';
	
	public static function load($model, $ext='.php'){
		NFS::load(MODEL_ROOT.$model.'Model'.$ext);
		$class = $model.'Model';
		return new $class();
	}
	
	public function getAll($where, $fields='*'){
		return DB::fetchAll(self::buildSelect($where, $fields), array_values($where));
	}
	
	public function getOne($where, $fields='*'){
		$sql = self::buildSelect($where, $fields).' LIMIT 1';
		return DB::fetch($sql, array_values($where));
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
	
	protected function buildWhere($where){
		$keys = ' 1=1 ';
		foreach ($where as $k=>$v){
			$keys .= " and $k=?";
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
}