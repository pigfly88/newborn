<?php
class model extends component {
	protected $db;
	protected $dbobj;
	protected $auto;
	protected $table;
    protected $mongo;

	public $columns = null;
    public $prefix;
    public $debug = 0;
	protected $sql = null;
	public $last_sql;
	
	protected function _init(){
		//$db = $this->db ? $this->db : 'mysql.default';
		//$this->dbobj = db::driver($db);
	}
	
	/**
	 * 自动填充
	 * @param array $query
	 */
	protected function auto(&$query, $func='find'){
		is_array($this->auto[$func]) && is_array($query) && $query = array_merge($this->auto[$func], $query);
	}
	
	public function execute($sql){
		return db::execute($sql);
	}
	
	public function fields($fields){
		is_array($friends) && $fields = implode(', ', $fields);
		$this->sql['fields'] = $fields;
		return $this;
	}
	
	public function table($table){
		$this->sql['table'] = $table;
		return $this;
	}
	
	public function where($where){
		$this->sql['where'] = $where;
		return $this;
	}
	
	public function orderby($orderby){
		$this->sql['orderby'] = $orderby;
		return $this;
	}
	
	public function limit($limit){
		$this->sql['limit'] = $limit;
		return $this;
	}

	public function get(){
		return db::get($this->sql(__FUNCTION__));
	}
	
	public function insert($data){
		//$this->auto($data, __FUNCTION__);
		return db::execute($this->sql(__FUNCTION__, $data));
	}
	
	public function getall(){
		//$this->auto($query, 'select');
		return db::getall($this->sql(__FUNCTION__));
	}
	
	public function update($data){
		return db::execute($this->sql(__FUNCTION__, $data));
	}
	
	public function delete(){
		return db::execute($this->sql(__FUNCTION__));
	}

	protected function sql($method='get', $data=null){
		$fields = empty($this->sql['feilds']) ? '*' : $this->sql['feilds'];
		$table = $this->sql['table'] ? $this->sql['table'] : $this->table; 
		if(in_array($method, array('get', 'getall'))){
			$sql = "SELECT {$fields} FROM {$table}";
		}else if($method=='update'){
			foreach ($data as $k=>$v){
				is_string($v) && $v="'{$v}'";
				$set.="`{$k}`={$v}";
			}
			$sql = "UPDATE {$table} SET {$set}";
		}else if($method=='delete'){
			$sql = "DELETE FROM `{$table}`";
		}else if($method=='insert'){
			foreach ($data as $k=>$v){				
				$key[]="`{$k}`";
				is_string($v) && $v="'{$v}'";
				$value[]=$v;
			}
			$keystr = implode(', ', $key);
			$valuestr = implode(', ', $value);
			$sql = "INSERT INTO {$table} ({$keystr}) VALUES ({$valuestr})";
		}
		if($this->sql['where'])	$sql.=" WHERE {$this->sql['where']}";
		if($this->sql['orderby'])	$sql.=" ORDER BY {$this->sql['orderby']}";
		if($this->sql['groupby'])	$sql.=" GROUP BY {$this->sql['groupby']}";
		if($this->sql['limit'])	$sql.=" LIMIT {$this->sql['limit']}";
		
		$this->last_sql = "<i>{$sql}</i>".PHP_EOL;
		$this->sql = array();
		return $sql;
	}
	
	public function get_last_sql(){
		
	}
}