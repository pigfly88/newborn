<?php
//namespace nfs\cache;
class cache {
	protected static $cache=array();
	
	public static function init($config){
		if(empty($config)) throw new NFSException('Config Is Empty');
		
		if(is_array($config) && !empty($config)){
			foreach($config as $k=>$v){
				if(empty($v['timeout']))	$v['timeout']=8;
				$name = strtolower($k);
				if(isset(self::$cache[$name]))	return self::$cache[$name];
				
				switch ($name){
					case 'memcache':
						if(!extension_loaded('memcache')){
							throw new NFSException('Redis Extension Not Found');
						}
						$memcache = new Memcache;
						if(empty($v['port']))	$v['port']=11211;
						$memcache->connect($v['host'], $v['port'], $v['timeout']);
						self::$cache[$name] = $memcache;
					break;
					
					case 'redis':
						if(!extension_loaded('redis')){
							throw new NFSException('Redis Extension Not Found');
							return;
						}
						$redis = new Redis();
						if(empty($v['port']))	$v['port']=6379;
						$redis->connect($v['host'], $v['port'], $v['timeout']);
						self::$cache[$name] = $redis;
					break;
					
					default:
						throw new NFSException('Unknown Cache Type');
					break;
				}
			}
		}else{
			$name = strtolower($config);
			F('');
			if(isset(self::$cache[$name]))	return self::$cache[$name];
		}
	}

}