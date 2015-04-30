<?php
class index_m extends model{
	protected $table='user';
	protected $cache;
    
    public function __init(){
         
        //$this->cache = NFS::helper('Cache/CacheMongo', 'mongodb://localhost:27017', array('timeout'=>1000, 'socketTimeoutMS'=>2));
    }
    
	public function get(){
       echo __METHOD__.'()<br />';
        //$res = $this->cache->insert('test.article', array('name'=>'zhupp'));
        //var_dump($res);
        $cdt = array(
            'q'=>array(
                //'name'=>'zhupp', 
                //'age'=>array('$lte'=>30, '$gte'=>20)
            ), 
            'f'=>array('name', 'age'), 
            'o'=>array('age'=>-1),
            //'l'=>2,
            //'s'=>2,
        );
        //$res = $this->cache->find('test.article', $cdt);
        //var_dump($res);exit;
		//$res = $this->getOne(array('id'=>1));
		return $res;
	}
	
	
	
}