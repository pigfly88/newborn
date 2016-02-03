<?php !defined('BOYAA') AND exit('Access Denied!');
/**
 * redis落地类
 * @author OuyangLiu
 * 特别注意：1、新加了更多的方法支持。
 * 			2、现已全面支持del、expire、expireAt三个特殊关键字，为保证数据完整性，使用此3个操作时会实时同步到数据库，会有一定的时间消耗
 */
class storage{
    
	public $redisType;
	public $redis;
	public $expire;
	
	public function __construct($type){
		$this->redisType = $type;
		$this->redis = by::redis($this->redisType);
	}
	
	public function set(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, array_shift($aArg));
		return $flag;
	}
	
	public function mset(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		$aKey = array_shift($aArg);
		foreach ($aKey as $key=>$val){
			by::mstorage()->saveKey($this->redisType, $key);
		}
		return $flag;
	}
	
	public function append(){
		$aArg = func_get_args();
		$firstKey = reset($aArg);
		if(!$this->redis->EXISTS($firstKey)){
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, $firstKey);
		return $flag;
	}
	
	public function incr(){
		$aArg = func_get_args();
		$firstKey = reset($aArg);
		if(!$this->redis->EXISTS($firstKey)){
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, $firstKey);
		return $flag;
	}
	
	public function incrby(){
		$aArg = func_get_args();
		$firstKey = reset($aArg);
		if(!$this->redis->EXISTS($firstKey)){
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, $firstKey);
		return $flag;
	}
	
	public function decr(){
		$aArg = func_get_args();
		$firstKey = reset($aArg);
		if(!$this->redis->EXISTS($firstKey)){
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, $firstKey);
		return $flag;
	}
	
	public function decrby(){
		$aArg = func_get_args();
		$firstKey = reset($aArg);
		if(!$this->redis->EXISTS($firstKey)){
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, $firstKey);
		return $flag;
	}
	
	public function get(){
		$aArg = func_get_args();
		$result = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		if($result === false){
			$firstKey = array_shift($aArg);
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->SET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		return $result;
	}
	
	public function hset(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, array_shift($aArg), array_shift($aArg));
		return $flag;
	}
	
	public function hincrby(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, array_shift($aArg), array_shift($aArg));
		return $flag;
	}
	
	public function hincrbyfloat(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, array_shift($aArg), array_shift($aArg));
		return $flag;
	}
	
	public function hmset(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->saveKey($this->redisType, array_shift($aArg));
		return $flag;
	}
	
	public function hdel(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		by::mstorage()->delOne($this->redisType, array_shift($aArg), array_shift($aArg));
		return $flag;
	}
	
	public function hget(){
		$aArg = func_get_args();
		$result = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		if($result === false){
			$firstKey = array_shift($aArg);
			$nextKey = array_shift($aArg);
			if(!$this->redis->EXISTS($firstKey)){
				$data = by::mstorage()->getFromDb($this->redisType, $firstKey, $nextKey);
				if(is_array($data)){
					list($result, $expire) = $data;
					$this->redis->HSET($firstKey, $nextKey, $result);
					$this->redis->EXPIREAT($firstKey, $expire);
				}
			}
		}
		return $result;
	}
	
	public function hgetall(){
		$aArg = func_get_args();
		$result = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		if(empty($result)){
			$firstKey = array_shift($aArg);
			$data = by::mstorage()->getFromDb($this->redisType, $firstKey);
			if(is_array($data)){
				list($result, $expire) = $data;
				$this->redis->HMSET($firstKey, $result);
				$this->redis->EXPIREAT($firstKey, $expire);
			}
		}
		return $result;
	}
	
	public function expire(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		$firstKey = array_shift($aArg);
		$nextKey = array_shift($aArg);
		by::mstorage()->setExpire($this->redisType, $firstKey, $nextKey == -1 ? 0 : $nextKey+time());
		return $flag;
	}
	
	public function expireat(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		$firstKey = array_shift($aArg);
		$nextKey = array_shift($aArg);
		by::mstorage()->setExpire($this->redisType, $firstKey, $nextKey);
		return $flag;
	}
	
	public function del(){
		$aArg = func_get_args();
		$flag = call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
		foreach ($aArg as $key){
			by::mstorage()->setExpire($this->redisType, $key, time());
		}
		return $flag;
	}
	
	public function exists(){
		$aArg = func_get_args();
		return call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
	}
	
	public function ttl(){
		$aArg = func_get_args();
		return call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
	}
	
	public function keys(){
		$aArg = func_get_args();
		return call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
	}
	
	public function type(){
		$aArg = func_get_args();
		return call_user_func_array(array($this->redis, __FUNCTION__), $aArg);
	}
}
