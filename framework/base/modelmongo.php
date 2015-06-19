<?php
class modelmongo extends model {
	protected $db;
	protected $auto;

	public $columns = null;
    public $prefix = '';
    public $debug = 0;//开启debug模式将会记录sql
	protected static $models;
	
	public function __construct($opt){
		$this->db = oo::mongo()->connect($opt);
	}
	
	public function mongo(){
		return $this->db;
	}
	
	
	
	
	
}