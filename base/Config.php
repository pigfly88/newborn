<?php
/**
 * Description of Config
 *
 * @author BarryZhu
 */
class Config {
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