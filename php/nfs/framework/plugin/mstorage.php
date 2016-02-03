<?php !defined('BOYAA') AND exit('Access Denied!');
/**
 * redis落地model类
 * @author OuyangLiu
 */
class Model_Mstorage extends Model_Tables{
    
	public $interval = 900;		//时间间隔
    public $separate = '^_^';	//专业分隔符，不要问我为什么
    public $redis = null;		//有变动的key数据存储的位置
    public $try = 3;			//添加时重试的次数
    
    public function __construct(){
    	$this->redis = by::redis('storage');
    	if(ENVID != 3) $this->interval = 10; //测试服10秒回写
    }
    
	/**
	 * 获取当前的存储key
	 * @param bool $isAll
	 */
	private function getThisKey($isAll = false){
		$now = time();
		$fix = $isAll ? '*' : intval($now - $now%$this->interval);
		return Model_Keys::getStorageKey($fix);
	}
	
	/**
	 * 获取分表
	 * @param string $key
	 */
	private function getThisTable($key){
		return $this->storage.(hexdec(substr(sha1( $key ), 0, 4))%100);
	}
	
	/**
	 * 获取拼接key
	 */
	private function getSepKey($aKey){
		return is_array($aKey) ? implode($this->separate, $aKey) : '';
	}
	
	/**
	 * 过滤key里的特殊字符
	 * @param string $key
	 */
	private function makeValidKey($key){
		return str_replace(array('\\', '\'', '"', '%', '?', '*'), '', (string)$key);
	}
	
	/**
	 * 保存操作key
	 * @param array $arguments
	 */
    public function saveKey($type, $key, $field=''){
    	$thisKey = $this->getSepKey(array($key, $type, $field));
    	for ($i=0; $i<$this->try; $i++){
    		$flag = $this->redis->sAdd($this->getThisKey(), $thisKey);
    		if($flag) return $flag;
    	}
    	return false;
    }
    
    /**
     * 删除一条数据
     */
    public function delOne($type, $key, $field){
    	$key = $this->getSepKey(array($key, $type));
    	$table = $this->getThisTable($key);
    	$field = by::db_storage()->escape($field);
    	$sql = "DELETE FROM {$table} WHERE `key`='{$key}' AND `field`='{$field}' limit 1";
    	return by::db_storage()->query($sql);
    }
    
    /**
     * 获取下一个要处理的key
     */
    public function getNextKey(){
    	$allKeys = $this->redis->KEYS($this->getThisKey(true));
    	if(!empty($allKeys) && is_array($allKeys)){
    		if(count($allKeys) >= 5){
    			fc::debug(date('Ymd H:i:s')."|allKeys_too_long|keys:".implode(',', $allKeys), 'storage_err.txt');
    		}
	    	sort($allKeys);
	    	$now = time();
	    	foreach ($allKeys as $value){
		    	list($key, $time) = explode('|', $value);
		    	if(($now - $time) < $this->interval){
		    		 return false;
		    	}
		    	$num = $this->redis->SCARD($value);
		    	if($num <= 0){
		    		$this->redis->DEL($value);
		    		continue;
		    	}
		    	return $value;
	    	}
    	}
    	return false;
    }
    
    /**
     * 随机取一个元素
     * @param string $key
     */
    public function getOneByKey($key){
    	return $this->redis->SRANDMEMBER($key);
    }
    
    /**
     * 删除一个元素
     * @param string $key
     * @param string $doKey
     */
    public function deleteOne($key, $doKey){
    	return $this->redis->sRemove($key, $doKey);
    }
    
    /**
     * 入库
     * @param string $thekey
     */
    public function redis2db($key){
    	list($thekey, $type, $field) = explode($this->separate, $key);
    	$thisRedis = by::redis($type);
    	$keyType = $thisRedis->TYPE($thekey);
    	$thisKey = $this->getSepKey(array($thekey, $type));
    	$theValue = array();
    	$now = time();
    	$flag = 0;
    	switch ($keyType){
    		case 1: //'string'
    			$theValue[$this->separate] = $thisRedis->GET($thekey);
    			break;
//    		case 2: //'set'
//    	    	$thisNum = $thisRedis->SCARD($thekey);
//    			if($this->maxNum < $thisNum){
//    				fc::debug(date('Ymd H:i:s')."|storage_num_err|redis:$type|type:$keyType|key:$thekey|num:$thisNum", 'storage_err.txt');
//    				return false;
//    			}
//    			$theValue = $thisRedis->SMEMBERS($thekey);
//    			break;
//    		case 4: //'zset'
//    			$thisNum = $thisRedis->ZCARD($thekey);
//    			if($this->maxNum < $thisNum){
//    				fc::debug(date('Ymd H:i:s')."|storage_num_err|redis:$type|type:$keyType|key:$thekey|num:$thisNum", 'storage_err.txt');
//    				return false;
//    			}
//    			$theValue = $thisRedis->ZRANGE($thekey, 0, $thisNum);
//    			break;
    		case 5: //'hash'
    			$firstValue = $this->getDb($thisKey, $this->separate);
    			if(!empty($firstValue) && ($firstValue['expire'] <= $now)){ //如果原来的值已过期则重新设置所有
    				$this->delByKey($thisKey);
    				$field = '';
    			}
    			if($field){
    				$theValue[$field] = $thisRedis->HGET($thekey, $field);
    			}else{
    				$theValue = $thisRedis->HGETALL($thekey);
    			}
    			$theValue[$this->separate] = $this->separate; //保证所有的数据都有一个field = $this->separate 的值，方便过期时间的设置和删除key的操作
    			break;
    		default:
    			break;
    	}
    	if(!empty($theValue) && is_array($theValue)){
    		$expire = $thisRedis->TTL($thekey);
    		$expire = ($expire == -1) ? 0 : time()+$expire;
    		foreach ($theValue as $k=>$v){
				$flag += $this->setDb($thisKey, $v, $expire, $k);
    		}
    	}else{ //数据不对默认成功
    		$flag ++;
    	}
    	return (bool)$flag;
    }
    
    
    /**
     * 数据入库
     * @param string $key
     * @param $value
     * @param int $expire
     */
    private function setDb($key, $value, $expire, $field){
		$key = by::db_storage()->escape($key);
		$field = by::db_storage()->escape($field);
    	$value = by::db_storage()->escape(serialize($value));
    	$expire = Helper::uint($expire);
    	$table = $this->getThisTable($key);
    	
    	$sql = "INSERT INTO {$table} SET `key`='$key',`field`='$field',`value`='$value',`expire`=$expire ON DUPLICATE KEY UPDATE `value`='$value',`expire`=$expire";
    	return by::db_storage()->query($sql);
    }
    
    /**
     * 数据入库
     * @param string $key
     * @param $value
     * @param int $expire
     */
    public function setExpire($type, $thekey, $expire){
    	$key = $this->getSepKey(array($thekey, $type));
		$key = by::db_storage()->escape($key);
    	$expire = Helper::uint($expire);
    	$table = $this->getThisTable($key);
    	$sql = "UPDATE {$table} SET `expire`=$expire WHERE `key`='$key' AND `field`='{$this->separate}'";
    	return by::db_storage()->query($sql);
    }
    
    /**
     * 从db中取一条记录
     * @param string $key
     */
    private function getDb($key, $field){
    	$key = by::db_storage()->escape($key);
    	$table = $this->getThisTable($key);
    	$field = by::db_storage()->escape($field);
    	$sql = "SELECT value,field,expire FROM {$table} WHERE `key`='{$key}'";
    	if($field){
    		$or = ($field == $this->separate) ? "" : "OR `field`='{$this->separate}'";
    		$sql .= " AND (`field`='{$field}' {$or})";
    	}
    	return by::db_storage()->getAll($sql);
    }
    
    /**
     * 从db取数据
     * @param string $type
     * @param string $thekey
     */
    public function getFromDb($type, $thekey, $field=''){
    	$key = $this->getSepKey(array($thekey, $type));
    	$result = $this->getDb($key, $field);
    	if(empty($result)) return false;
    	$aData = array();
    	$expire = 0;
    	foreach ($result as $value){
    	    $data = unserialize($value['value']);
	    	if($data === false){
	    		fc::debug(date('Ymd H:i:s')."|storage_dbdata_err|table:$table|key:$key|field:$field", 'storage_err.txt');
	    		return false;
	    	}
	    	if($value['field'] == $this->separate) $expire = $value['expire'];
	    	$aData[$value['field']] = $data;
    	}
		if(empty($aData)) return false;
		if($expire <= 0){
			$expire = -1;
		}elseif($expire <= time()){
			return false;
		}
		if(count($aData) <= 1){ //string
			$aData = reset($aData);
			if($aData == $this->separate){ //被hdel后的最后一个hash
				$this->setExpire($type, $thekey, time());
				return false;
			}
		}elseif($field){ //hash
			$aData = $aData[$field];
		}else{ //hash
			unset($aData[$this->separate]); //这条记录不要返回
		}
    	return array($aData, $expire);
    }
    
    /**
     * 清理过期数据
     */
    public function cleanUpData(){
    	$now = time();
    	$limit = 1000;
    	$aCleanData = array();
    	for ($i=0; $i<100; $i++){
    		$table = $this->storage.$i;
    		$sql = "SELECT `key` FROM {$table} WHERE `expire`>0 AND `expire`<={$now} AND `field`='{$this->separate}' limit {$limit}";
    		$aData = by::db_storage()->getAll($sql);
    		foreach ($aData as $val){
    			$thisKey = $val['key'];
    			$rows = $this->delByKey($thisKey);
    			$aCleanData["s$i"] = $rows;
    		}
    		sleep(1);
    	}
    	fc::debug(date('Ymd H:i:s')."|".json_encode($aCleanData), 'cleanUpData.txt');
    	return true;
    }
    
    /**
     * 根据key删除内容
     * @param string $thisKey
     */
    private function delByKey($thisKey){
    	if(!$thisKey = by::db_storage()->escape($thisKey)){
    		return 0;
    	}
    	$table = $this->getThisTable($thisKey);
    	$sql = "DELETE FROM {$table} WHERE `key`='{$thisKey}'";
    	by::db_storage()->query($sql);
    	return by::db_storage()->affectedRows();
    }
}
