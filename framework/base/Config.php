<?php
/**
 * Description of Config
 *
 * @author BarryZhu
 * 
 * @uses
 * $config = new Config('file');
 * $data = array('time'=>date('Y-m-d H:i:s'));
 * $config->set($data, 'config.php');
 * //$config->set($data, 'cache');
 */
class config {
    public $type=0;
    public $file;
    public $config = array();
    
    public function set($k, $v){
        $k = explode('.', $k);

        $key = implode($glue, $pieces);
        //self::$config[$key];

        //$key = implode($glue, $pieces)
        //self::$config[$key]

        return self::$config = array_merge(self::$config, $data);
    }
    
    public function get($k){
        
    }





}