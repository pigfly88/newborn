<?php

include_once PATH_MOD . 'omongotable.php';

class mumongo{
	private $replicaSet;//集群名称
	private $servers;//数据库地址
	private $mongoCli;//数据库连接对象
	public $err = '';
	public $mgtblactstr = '';//活动表名
	public $mgtblactlist = '';//活动表名队列专用
	public $mgtblacthashs = '';//活动表名hash专用
	public $mgtblactset = '';//活动表名set专用
	public $mgtblactSortedSet = '';//活动表名Sorted Set专用
	public $expire = 4575715200;//活动表缓存时间
	/**
	 * [
	 * replicaSet  集群名称
	 * servers array(array('x.x.x.x',5000))  节点列表
	 * ]
	 **/
	public function __construct( $aSer, $aOpt = array()){
		if($aSer && is_string($aSer)){//简单配置写法
			$this->servers = $aSer;
			return;
		}
		if($aSer['replicaSet']){
			$this->replicaSet = $aSer['replicaSet'];
		}

		$aV = array();
		foreach((array)$aSer['servers'] as $row){
			$aV[] = $row[0] . ':' . $row[1];
		}

		$this->servers = implode(',', $aV);


		$this->mgtblactlist = oo::logs()->mgtblbase('act_list');
		$this->mgtblacthashs = oo::logs()->mgtblbase('act_hashs');
		$this->mgtblactset = oo::logs()->mgtblbase('act_set');
		if($aOpt['memSet']){
			//memcached数据
			$this->mgtblactstr = oo::logs()->getMongoDb('mcache') . '.mem';
		}else{
			$this->mgtblactstr = oo::logs()->mgtblbase('act_string');
		}

		$this->mgtblactSortedSet = oo::logs()->mgtblbase('act_SortedSet');
		
		if ( isset($aOpt['act_id']) && ($act_id = intval($aOpt['act_id'])) ) {
			$this->mgtblactlist = oo::logs()->mgtblact("{$act_id}_list");
			$this->mgtblacthashs = oo::logs()->mgtblact("{$act_id}_hashs");
			$this->mgtblactset = oo::logs()->mgtblact("{$act_id}_set");
			$this->mgtblactSortedSet = oo::logs()->mgtblact("{$act_id}_SortedSet");
			$aOpt['memSet'] or $this->mgtblactstr = oo::logs()->mgtblact("{$act_id}_string");
		}
	}
	//是否启用集群 长连接
	public function isReplicaSet(){
		return true;
	}
	//连接mongodb数据库
	public function doconn( $try){

		if(!$this->servers){
			return false;
		}

		if( ! is_object($this->mongoCli)){
			$servers = 'mongodb://' . $this->servers;
			$aOptions = array();//连接选项
			$aOptions['connect'] = true;//构造器是否应该在返回前连接
			$aOptions['connectTimeoutMS'] = 1000;//打开连接超时的时间
			$aOptions['socketTimeoutMS'] = 5000;//在套接字上发送或接收超时的时间。
			if($this->replicaSet && $this->isReplicaSet()){
				$aOptions['replicaSet'] = $this->replicaSet;//集群名称
			}

			//开关 bakIdcIsMaster 备机房是业务节点
			if((SERVER_TYPE === 'bak') && (oo::$config['bakIdcIsMaster'] <= 0)){
				//备机房 且 不是也是主节点
				$aOptions['readPreference'] = MongoClient::RP_SECONDARY;//是从副本节点读取
			}elseif((SERVER_TYPE === 'on') && (oo::$config['bakIdcIsMaster'] == 1)){
				//主机房 且 备机房是业务主节点(即主机房不是业务节点)
				$aOptions['readPreference'] = MongoClient::RP_SECONDARY;//是从副本节点读取
			}

			try{
				$this->mongoCli = new MongoClient($servers, $aOptions);
			}catch(MongoConnectionException $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}catch(Exception $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}
		//有对象 无连接
		}elseif(is_object($this->mongoCli) && ( ! is_array($this->mongoCli->getConnections()))){
			try{
				$this->mongoCli->connect();
			}catch(MongoConnectionException $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}catch(Exception $e){
				($try == 2) && $this->exceptionLog($e, "try:{$try} ", 0);
				$this->err = $e->getMessage();
				$this->mongoCli = null;
			}
		}
		if(is_object($this->mongoCli) && is_array($this->mongoCli->getConnections())){
			return true;
		}else{
			return false;
		}
	}
	//连接
	public function connect(){
		for($try = 0; $try < 3; $try++){
			if($try == 2){
				$this->mongoCli = null;
			}
			if($con = $this->doconn( $try)){
				break;
			}
		}
		return $con;
	}

	//获取已连接的数据库
	public function getConnections(){
		$arr = array();
		if(is_object($this->mongoCli)){
			$arr = $this->mongoCli->getConnections();
		}
		return $arr;
	}
	public function close(){
		$connections = $this->getConnections();
		foreach ( $connections as $con ){
			// 遍历所有连接，关闭
			$closed = $this->mongoCli->close( $con['hash'] );
		}
		return true;
	}
	//所有关联主机的状态信息
	public function getHosts(){
		$arr = array();
		if($this->connect()){
			$arr = $this->mongoCli->getHosts();
		}
		return $arr;
	}
	//列出所有有效数据库
	public function listDBs(){
		$arr = array();
		if($this->connect()){
			$arr = $this->mongoCli->listDBs();
		}
		return $arr;
	}
	//选择一个数据库，返回数据库对象
	public function selectDB( $db){
		$dbObj = null;
		if($db && $this->connect()){
			try{
				$dbObj = $this->mongoCli->selectDB( $db);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
				$dbObj = null;
			}

		}
		return $dbObj;
	}
	//解析出指定mongo数据库和集合
	public function explodeColl( $table){
		if( (! $table = trim($table)) || ( ! $aTale = explode('.', $table)) || (count($aTale) != 2)){
			return "table:$table is error. example:'texas_57.minfo'";
		}
		return $aTale;
	}
	//获取数据库的文档集
	public function selectCollection( $table){
		$aTale = $this->explodeColl( $table);
		if( ! is_array($aTale)){
			$this->err = $aTale;
			return null;
		}

		$db = $aTale[0];
		$coll = $aTale[1];

		$collObj = null;
		if($db && $coll && $this->connect()){
			try{
				$collObj = $this->mongoCli->selectCollection( $db, $coll);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
				$collObj = null;
			}
		}
		return $collObj;
	}
	//返回某个db的结果集的对象
	public function listCollections( $db){
		if($db && $dbObj = $this->selectDB($db)){
			try{
				$aRet = $dbObj->listCollections();
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}
		}
		return $aRet;
	}
	//返回某个db的结果集的数组
	public function getCollectionNames( $db){
		if($db && $dbObj = $this->selectDB($db)){
			try{
				$aRet = $dbObj->getCollectionNames();
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}
		}
		return $aRet;
	}
	//创建集合
	public function createCollection($table, $options = array()){
		if( ! $table){
			return false;
		}
		$aTale = $this->explodeColl( $table);
		if( ! is_array($aTale)){
			$this->err = $aTale;
			return false;
		}

		$db = $aTale[0];
		$coll = $aTale[1];
		$dbObj = $this->selectDB($db);
		if($dbObj){
			try{
				$objColl = $dbObj->createCollection($coll, $options);
			}catch(Exception $e){
				$this->exceptionLog($e, "");
			}

		}
		return $objColl;
	}
	//删除集合
	public function drop( $table){
		return true;
		$collObj = $this->selectCollection( $table);
		if( ! $collObj){
			return $this->genRet(0, false, $collObj, $this->err, __line__);
		}
		try{
			$ret = $collObj->drop();
			$sta = 1;
		}catch(MongoException $e){
			$this->exceptionLog($e, "drop");
			$errExcep = $e->getMessage();
			$sta = 0;
		}
		return $this->genRet(0, false, $ret, $errExcep, __line__);
	}
	//创建索引
	/**
	 * $table 表名 db.table
	 * $akeys 索引组合 array('v' => 1,'t' => -1)  建立v t 组合索引 v升序 t降序
				  {‘a’:1} 普通升序索引
  				  {‘a.city’:1，’b.people’:-1} 组合索引
		　　   {‘item.name’:1} 文档索引
	 *	$options  　　这里指定其他选项
　　						{backgroud:true}   索引创建在后台运行
					　　 {unique:true}	     创建唯一索引
					　　 {dropDups:true}	  创建唯一索引时，如有重复则删除第一次之后的
							{name}			     可指定索引名称
	 **/
	public function ensureIndex($table, $akeys, $options=array()){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, false, $collObj, $this->err, __line__);
		}
		$arr = $this->getIndexInfo($table);
		$exist = false;//是否存在
		foreach($arr['data'] as $row){
			if($akeys === $row['key']){
				$exist = true;
			}
		}
		if($exist){
			return $this->genRet(0, false, $collObj, 'Index exist', __line__);
		}

		$ret = array();
		$options['background'] = true;
		try{
			$ret = $collObj->ensureIndex($akeys, $options);
		}catch(MongoException $e){
			$this->exceptionLog($e, "ensureIndex");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "ensureIndex");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "ensureIndex");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "ensureIndex");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//获取索引信息
	public function getIndexInfo($table){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}

		try{
			$ret = $collObj->getIndexInfo();
			$sta = 1;
		}catch(MongoException $e){
			$this->exceptionLog($e, "getIndexInfo");
			$sta = 0;
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "getIndexInfo");
			$sta = 0;
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "getIndexInfo");
			$sta = 0;
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "getIndexInfo");
			$sta = 0;
			$errExcep = $e->getMessage();
		}
		return $this->genRet($sta, $ret, $ret, $errExcep, __line__);
	}
	//删除索引
	/**
	 * $table 表名 db.table
	 * $akeys 索引组合 array('v' => 1,'t' => -1)  建立v t 组合索引 v升序 t降序
				  {‘a’:1} 普通升序索引
  				  {‘a.city’:1，’b.people’:-1} 组合索引
		　　   {‘item.name’:1} 文档索引
	**/
	public function deleteIndex($table, $akeys){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$ret = array();
		try{
			$ret = $collObj->deleteIndex($akeys);
		}catch(MongoException $e){
			$this->exceptionLog($e, "deleteIndex");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "deleteIndex");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "deleteIndex");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "deleteIndex");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//删除集合所有索引
	public function deleteIndexes($table){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$ret = array();
		try{
			$ret = $collObj->deleteIndexes($table);
		}catch(MongoException $e){
			$this->exceptionLog($e, "deleteIndexes");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "deleteIndexes");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "deleteIndexes");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "deleteIndexes");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//查看当前文档集 某个字段有哪些值。
	public function distinct($table, $key, $options=array()){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, false, $collObj, $this->err, __line__);
		}
		$ret = array();
		try{
			$ret = $collObj->distinct($key, $options);
		}catch(MongoException $e){
			$this->exceptionLog($e, "distinct");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "distinct");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "distinct");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "distinct");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//插入数据
	/**
	 * $table 数据库.文档集\表
	 * $arr  数组 可以是多维的 如果 _id 存在则不会执行插入
	 * $safe 是否安全插入 false 离铉之箭 true 返回前刷新磁盘 很慢
	 * w 0 离铉之箭，不关心是否成功
		 1 写操作，会被服务器确认
		 N 写操作，主服务器必须确认，然后复制到N-1
		 majority 写操作需所有副本确认，是个特殊保留字符
		 j=true 写操作被主确认，并根据日志同步副本
	 * j 写日志的方式 0 异步写日志 1同步写日志
	 * 
	 * $lru 0 time(), 非0 time() + lru,最后更新时间
	 **/
	public function insert($table, $arr, $safe = false, $lru = 0){
		$microtime = ocache::debug( 0);
		$aTra = array();
		if( (is_string($arr['_id']) && strpos($arr['_id'], date('md')) !== false) && (strpos($table, date('ymd')) === false) && ((strpos($table, 'base_act') !== false) || (strpos($table, 'mem') !== false) || (strpos($table, 'logs_') !== false)  || (strpos($table, '_kvs') !== false) ) ){
			$aTra = debug_backtrace();
			$aTra = array_pop( $aTra); //取最后一条
			$callRes = implode("\n", array(date('Y-m-d H:i:s'), '[mongodb fata] '.$appmsg, json_encode($e), $_SERVER["PHP_SELF"], 'file:'.$aTra['file'], 'line:'.$aTra['line'], 'function:'.$aTra['function'], 'args:'.implode(',', (array)$aTra['args']) ) );
			(strpos($arr['_id'], 'USERTOOL') !== false || strpos($arr['_id'], 'ACT|640|DLOGIN|') !== false) or oo::logs()->debug(array($arr, $table, $callRes), 'mumongo.fatalerr.txt');
		}
		
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		if(empty($arr)){
			$this->exceptionLog($arr, "insert", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		
		if( in_array($table, $this->redisTables()) ){//对redis、memcache兼容的表,添加key最后操作时间
			( $lru = BY::uint($lru) ) || ( $lru = time() );
			isset($arr['lru']) || ( $arr['lru'] = $lru );			
		}

		try{
			$ret = $collObj->insert($arr, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch( MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoDuplicateKeyException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}

		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'insert', '-', $microtime, $table.'--'.json_encode($arr).'--'.json_encode($aTra) );
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//批量插入数据 (单纯的插入， 不会尝试去更新)
	/**
	 * $db   数据库
	 * $coll 文档集\表
	 * $arr  数组 可以是多维的 如果 _id 存在则不会执行插入
	 * $safe 是否安全插入 false 离铉之箭 true 返回前刷新磁盘 很慢
	 * w 0 离铉之箭，不关心是否成功
		 1 写操作，会被服务器确认
		 N 写操作，主服务器必须确认，然后复制到N-1
		 majority 写操作需所有副本确认，是个特殊保留字符
		 j=true 写操作被主确认，并根据日志同步副本
	 * j 写日志的方式 0 异步写日志 1同步写日志
	 * 
	 * $lru			0 time(), 非0 time() + lru,最后更新时间
	 **/
	public function batchInsert($table, $arr, $safe = false, $lru = 0){
		$microtime = ocache::debug( 0);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		$op['continueOnError'] = true;
		if(empty($arr)){
			$this->exceptionLog($arr, "batchInsert", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}

		if( in_array($table, $this->redisTables()) ){//对redis、memcache兼容的表,添加key最后操作时间
			foreach ((array)$arr as $k => $v){
				$v = (array)$v;
				( $lru = BY::uint($lru) ) || ( $lru = time() );
				isset($v['lru']) || ( $v['lru'] = $lru );
				$arr[$k] = $v;
			}
		}

		try{
			$ret = $collObj->batchInsert($arr, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch( MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(MongoDuplicateKeyException $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "insert");
			$errExcep = $e->getMessage();
		}

		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		$aTra = array();
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'batchInsert', '-', $microtime, $table.'--'.json_encode($arr).'--'.json_encode($aTra) );
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//如果对象存在数据库，则更新现有的数据库对象，否则插入对象。
	public function save($table, $arr, $safe = false){
		$collObj = $this->selectCollection( $table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;// FALSE 返回前刷入磁盘 FALSE. Forces the insert to be synced to disk before returning success
			$op['j'] = 0;//false 返回前写入日志
			$op['w'] = 1;
		}else{
			$op['fsync'] = true;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		if(empty($arr)){
			$this->exceptionLog($arr, "save", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		try{
			$ret = $collObj->save($arr, $op);
		}catch(Exception $e){
			$this->exceptionLog($e, "save");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//检查索引应用
	public function checkIndex($table, $criteria){
		if( empty( $criteria)){
			return true;
		}
		if(rand(1,100) >= 2){
			return true;
		}

		if(strpos($table, '_ac_') === false){
			if (preg_match('#act\d+_(string|list|hashs|set|SortedSet)$#i', $table)) {//texas_13_act.act123_SortedSet
				$cfgIndex = array();
				$cfgIndex[] = array('t' => 1);
				$cfgIndex[] = array('k' => 1);
				$cfgIndex[] = array('k' => 1, 't' => 1);
				$cfgIndex[] = array('k' => 1, 'hk' => 1);
				$cfgIndex[] = array('k' => 1, 's' => -1);
			} else {
				$aMongoTbl = omongotable::mongoIndexs();
				$cfgIndex = $aMongoTbl[$table]['keys'];
			}
		} else{
			$cfgIndex = array( array('k' => 1));
			$cfgIndex[] = array('from' => 1,'back' => 1);
			$cfgIndex[] = array('from' => 1,'t' => 1);
			$cfgIndex[] = array('to' => 1,'back' => 1);
			$cfgIndex[] = array('tit' => 1);
			$cfgIndex[] = array('type' => 1);
			$cfgIndex[] = array('mk' => 1);

			if (method_exists(oo::ac('base'), 'generateIndexes')) {
				$results = oo::ac('base')->generateIndexes($table, $criteria);
				if ($results) {
					$cfgIndex = array_merge($cfgIndex, $results);
				}
			}
		}

		( ! $cfgIndex) && ( $cfgIndex = array());
		$cfgIndex[] = array('_id' => 1);//添加默认_id 索引
		$isIndex = false;
		foreach($cfgIndex as $akey){
			$indexField = key($akey);
			$ret = $this->isIncludeIndex($indexField, $criteria);
			if($ret === true){
				$isIndex = true;
				break;
			}
		}
		return $isIndex;
	}
	//$indexField	索引字段
	//$criteria		查询条件
	public function isIncludeIndex($indexField, $criteria){
		if(( ! $indexField) || (!is_array($criteria))){
			return false;
		}
		$ret = false;
		foreach($criteria as $key => $row){
			//操作符
			if(substr($key, 0, 1) === '$'){
				$ret = $this->isIncludeIndex($indexField, $row);
			}elseif($key == $indexField){
				$ret = true;
				break;
			}
		}
		return $ret;
	}

	//更新数据
	/**
	 *$db 		数据库
	 *$coll 	文档集合 数据表
	 *$criteria 更新对象描述，即更新条件
	 * ==表达式运算符==
	 * $gt  大于
	 * $gte 大于等于
	 * $in  在指定的集合\集合中
	 * $nin 不在指定的集合\集合中
	 * $lt  小于
	 * $lte 小于等于
	 * $ne 不等于
	 * 等于则直接表示
	 * 语法：{field:{[$op]:value}}
	 * 如：array('uid' => 100,'age' => array('$gt' => 18,'$lt' => 30))
	 * 如上条件表示 uid为100 且 age大于18 且 age小于30
	 * ==逻辑运算符==
	 * $or  或     语法 {$or:{{field:{[$op]:value}},{field:{[$op]:value}}}}
	 * $and 且(所有满足)     语法 {$and:{{field:{[$op]:value}},{field:{[$op]:value}}}}
	 * $not 不匹配 语法 {field:$not:{{[$op]:value}}}
	 * $nor 执行逻辑NOR运算,指定一个至少包含两个表达式的数组，选择出都不满足该数组中所有表达式的文档。
	 * ==元素查询操作符==
	 * $exists 字段是否存在  true/false 语法 { field: { $exists: <boolean> } }
	 * $type   字段值类型 { field: { $type: <BSON type> } } http://docs.mongodb.org/manual/reference/operator/query/type/
	 * ==复杂操作符==
	 * $mod   取模操作 { field: { $mod: [ divisor, remainder ]} }  将字段值对divisor取模 等于 remainder
	 * $regex 正则操作
	 * $where 支持javascript
	 * ==数组查询==
	 * $all 匹配那些指定键的键值中包含数组，而且该数组包含条件指定数组的所有元素的文档。
	 *       { field: { $all: [ <value> , <value1> ... ] }
	 *
	 * $new_object 更新对象
	 * 操作符
	 * $set 覆盖式更新 {$set:{<field>:<value>}}}
	 * $inc 累加(正数)/减(负数)操作，支持整形和浮点数 不可对非数值类型字段进行此操作 {$inc:{<field>:<value>}}}
	 *	$unset	移除指定字段{‘$unset’:{<field>:1}}
	 * $rename 修改字段名{‘$rename’:{<oldfield>:<newfield>}} 不可在同一语句中改值又改名
	 * 数组操作符：
	 * $push 往数组中增加元素{$push :{<field>:<value>}}
	 * $pop数组头(-1)/尾(1)移除元素 {‘$pop:{<field>:<1/-1>}}
	 * $addToSet 往数组增加不存在的元素，相当于集合
	 *
	 * $options  选项
	 * upsert 		true 不存在则插入， false不存在不插入 默认为 true
	 * multiple 	true 更新满足条件的多条记录，false只更新首条 默认为true
	 * safe			true 需要获得服务器确认， false 不需要服务器确认，性能极佳  默认false
	 * lru			0 time(), 非0 time() + lru,最后更新时间
	 **/
	public function update($table, $criteria, $new_object, $options = array(), $lru = 0){
		$microtime = ocache::debug( 0);
		$aTra = array();
		if( (is_string($criteria['_id']) && strpos($criteria['_id'], date('md')) !== false) && (strpos($table, date('ymd')) === false) && ((strpos($table, 'base_act') !== false) || (strpos($table, 'mem') !== false) || (strpos($table, 'logs_') !== false)  || (strpos($table, '_kvs') !== false) ) ){
			$aTra = debug_backtrace();
			$aTra = array_pop( $aTra); //取最后一条
			$callRes = implode("\n", array(date('Y-m-d H:i:s'), '[mongodb fata] '.$appmsg, json_encode($e), $_SERVER["PHP_SELF"], 'file:'.$aTra['file'], 'line:'.$aTra['line'], 'function:'.$aTra['function'], 'args:'.implode(',', (array)$aTra['args']) ) );
			(strpos($$criteria['_id'], 'USERTOOL') !== false || strpos($criteria['_id'], 'ACT|640|DLOGIN|') !== false) or oo::logs()->debug(array($criteria, $table, $callRes), 'mumongo.fatalerr.txt');
		}
		
		$sactime = microtime(true);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		// 1 更新 不存在 则插入
		$op['upsert'] = ($options['upsert'] === false) ? false : true;
		// 1 批量更新
		$op['multiple'] = isset($options['multiple']) && in_array($options['multiple'], array(0, 1)) ? $options['multiple'] : 1;
		$safe = false;
		if(isset($options['safe'])){
			$safe = $options['safe'];
		}
		if($safe){
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		//目前只支持 '$set', '$unset', '$inc','$rename'
		$aModifiers = array('$set', '$unset', '$inc', '$push', '$pop', '$pull',
							'$addToSet', '$each','$setOnInsert','$rename','$min','$max');
		$object = $new_object;
		foreach($new_object as $act => $row){
			if( ! in_array($act, $aModifiers, true)){
				unset($new_object[$act]);
				continue;
			}
		}		
		if(empty($new_object)){
			$this->exceptionLog($object, "save", 1000);
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		
		if( in_array($table, $this->redisTables()) ){//对redis、memcache兼容的表,添加key最后操作时间
			( $lru = BY::uint($lru) ) || ( $lru = time() );
			is_array($new_object['$set']) || ( $new_object['$set'] = array() );
			isset($new_object['$set']['lru']) || ( $new_object['$set']['lru'] = $lru );			
		}
		
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$ret = $collObj->update($criteria, $new_object, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "update");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		$eactime = microtime(true);
		$this->activeTime('update', $eactime - $sactime, 0);
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'update', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($new_object).'--'.json_encode($aTra) );
		return $this->genRet($sta, (int)$ret['n'], $ret, $errExcep, __line__);
	}
	//删除记录
	// $justOne 只删一条
	public function remove($table, $criteria, $justOne = false, $safe = false){
		$microtime = ocache::debug( 0);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		$op = $ret = array();
		if($safe){
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 1;
		}else{
			$op['fsync'] = false;
			$op['j'] = 0;
			$op['w'] = 0;
		}
		$op['justOne'] = $justOne ? 1 : 0;

		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$ret = $collObj->remove($criteria, $op);
			if( ! is_array($ret)){
				$tempRet = intval($ret);
				$ret = array();
				$ret['ok'] = $tempRet;
			}
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}catch(MongoCursorTimeoutException $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "remove");
			$errExcep = $e->getMessage();
		}
		if($ret['ok'] && ( ! $ret['err'])){
			$sta = 1;
		}else{
			$sta = 0;
		}
		$aTra = array();
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'remove', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($aTra) );
		return $this->genRet($sta, $sta, $ret, $errExcep, __line__);
	}
	//更新并返回  $criteria, $new_object 见update说明
	public function findAndModify($table, $criteria, $new_object, array $fields = array(), array $options = array(), $lru = 0){
		$microtime = ocache::debug( 0);
		$aTra = array();
		if( (is_string($criteria['_id']) && strpos($criteria['_id'], date('md')) !== false) && (strpos($table, date('ymd')) === false) && ((strpos($table, 'base_act') !== false) || (strpos($table, 'mem') !== false) || (strpos($table, 'logs_') !== false)  || (strpos($table, '_kvs') !== false) ) ){
			$aTra = debug_backtrace();
			$aTra = array_pop( $aTra); //取最后一条
			$callRes = implode("\n", array(date('Y-m-d H:i:s'), '[mongodb fata] '.$appmsg, json_encode($e), $_SERVER["PHP_SELF"], 'file:'.$aTra['file'], 'line:'.$aTra['line'], 'function:'.$aTra['function'], 'args:'.implode(',', (array)$aTra['args']) ) );
			(strpos($criteria['_id'], 'USERTOOL') !== false || strpos($criteria['_id'], 'ACT|640|DLOGIN|') !== false) or oo::logs()->debug(array($criteria, $table, $callRes), 'mumongo.fatalerr.txt');
		}
		
		$sactime = microtime(true);
		$collObj = $this->selectCollection( $table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$op = $aRet = array();
		// 删除 并返回
		if(isset($options['remove'])){
			$op['remove'] = $options['remove'] ? true : false;
		}
		//更新
		if(isset($options['update'])){
			$op['update'] = $options['update'];
		}
		//为TRUE时，返回修改后的文件，而不是原来的。该findAndModify方法会忽略删除操作的新选项。默认值为FALSE。

		$op['new'] = ($options['new'] === false) ? false : true;

		//使用与更新域结合。为TRUE时，如果查询没有返回的文档，findAndModify命令创建一个新的文档，
		//默认值为false。在MongoDB中2.2中，findAndModify命令将返回NULL更新插入时为TRUE。
		$op['upsert'] = ($options['upsert'] === false) ? false : true;
		//排序
		if(isset($options['sort'])){
			$op['sort'] = $options['sort'];
		}

		$safe = false;
		if(isset($options['safe'])){
			$safe = $options['safe'];
		}

		$aModifiers = array('$set', '$unset', '$inc', '$push', '$pop', '$pull', '$ne',
							'$addToSet', '$each','$setOnInsert','$rename');
		foreach($new_object as $act => $row){
			if( ! in_array($act, $aModifiers, true)){
				unset($new_object[$act]);
				continue;
			}
		}
		
		if( in_array($table, $this->redisTables()) ){//对redis、memcache兼容的表,添加key最后操作时间
			( $lru = BY::uint($lru) ) || ( $lru = time() );
			is_array($new_object['$set']) || ( $new_object['$set'] = array() );
			isset($new_object['$set']['lru']) || ( $new_object['$set']['lru'] = $lru );			
		}
		
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$aRet = $collObj->findAndModify($criteria, $new_object, $aFields, $op);
		}catch(MongoResultException $e){
			$this->exceptionLog($e, "findAndModify");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "findAndModify");
			$errExcep = $e->getMessage();
		}
		$eactime = microtime(true);
		$this->activeTime('findAndModify', $eactime - $sactime, 0);
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'findAndModify', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($new_object).'--'.json_encode($aTra) );
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}
	//查询单条记录
	/**
	 * $criteria 查询对象描述，即查询条件条件  见 update
	 **/
	public function findOne($table, $criteria, $fields = array()){
		$microtime = ocache::debug( 0);
		$sactime = microtime(true);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $k => $f){
				if(is_array($f) && !empty($f)){//支持$slice array('v'=>array('$slice'=>2))
					$aFields[$k] = $f;
				}else{
					$aFields[$f] = 1;
				}
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$aRet = $collObj->findOne($criteria, $aFields);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "findOne");
			$errExcep = $e->getMessage();
		}
		$eactime = microtime(true);
		$this->activeTime('findOne', $eactime - $sactime, 0);
		$aTra = array();
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'findOne', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($aTra) );
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}

	//查询单条记录
	public function getOne($table, $criteria, $fields = array(), &$err = array()){
		$aMongoRet = $this->findOne($table, $criteria, $fields);		
		if(($aMongoRet['sta'] == 1) && $aMongoRet['data']){
			return (array)$aMongoRet['data'];
		}
		if( $aMongoRet['err'] ){
			$err = $aMongoRet;
		}
		return array();
	}
	//统计文档符合条件的文档数
	public function count($table, $criteria, $limit = 0, $skip = 0){
		$microtime = ocache::debug( 0);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$count = $collObj->count($criteria, $limit, $skip);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "count");
			$errExcep = $e->getMessage();
		}
		$aTra = array();
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'count', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($aTra) );
		return $this->genRet($errExcep ? 0 : 1, $count, '', $errExcep, __line__);
	}
	//查询记录数
	public function getCount($table, $criteria, $limit = 0, $skip = 0){
		$aMongoRet = $this->count($table, $criteria, $limit, $skip);
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			return (int)$aMongoRet['data'];
		}
		return 0;
	}
	//聚合运算
	public function group($table, $keys, $initial ,$reduce, $options){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		try{
			$aRet = $collObj->group($keys, $initial ,$reduce, $options);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "group");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}
	
	public function aggregate($table, $options){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, 0, $collObj, $this->err, __line__);
		}
		try{
			$aRet = $collObj->aggregate($options);
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "aggregate");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "aggregate");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "aggregate");
			$errExcep = $e->getMessage();
		}
		return $this->genRet($errExcep ? 0 : 1, $aRet, '', $errExcep, __line__);
	}
	//查询
	/**
	 * $criteria 一般查询见 update 条件规则
	 * ==空间查询== http://docs.mongodb.org/manual/reference/operator/query/geoWithin/
	 **/
	public function find($table, $criteria, $fields = array(), $aSort = array(), $aLimit = array()){
		$microtime = ocache::debug( 0);
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$aData = array();
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$retCursor = $collObj->find($criteria, $aFields);
			if( (!empty($aSort) ) && is_array($aSort)){
				foreach($aSort as $k => $v){
					$aSort[$k] = $v > 0 ? 1 : -1;//1升序 -1 降序
				}
				$retCursor = $retCursor->sort( $aSort);
			}
			//跳过或限制返回数量
			if( (!empty($aLimit) ) && is_array($aLimit)){
				list($skip, $limit) = $aLimit;
				if($skip > 0){
					$retCursor = $retCursor->skip( $skip);
				}
				if($limit > 0){
					$retCursor = $retCursor->limit( $limit);
				}
			}
			while($arr = $retCursor->getNext()){
				$arr['_id'] = $retCursor->key();
				$aData[] = $arr;
			}
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "find");
			$errExcep = $e->getMessage();
		}
		$aTra = array();
		($_GET['debug'] == 'bydebug') && $aTra = debug_backtrace();
		$aTra && ocache::debug( 1, 'mumongo', 'find', '-', $microtime, $table.'--'.json_encode($criteria).'--'.json_encode($aTra) );
		return $this->genRet($errExcep ? 0 : 1, $aData, '', $errExcep, __line__);
	}
	//查询多条记录
	public function getAll($table, $criteria, $fields = array(), $aSort = array(), $aLimit = array()){
		$aMongoRet = $this->find($table, $criteria, $fields, $aSort, $aLimit);
		if(($aMongoRet['sta'] == 1) && $aMongoRet['data']){
			return (array)$aMongoRet['data'];
		}
		return array();
	}
	//返回处理
	public function genRet($sta, $data, $resRet, $err = ''){
		$aRet = array();
		$aRet['sta'] = $sta;
		$aRet['data'] = $data;
		$aRet['resRet'] = $resRet;
		$aRet['err'] = $err;
		return $aRet;
	}
	public function exceptionLog($e, $appmsg, $errLevel = 100){
		$callRes = '';
		if($errLevel == 1000){
			$aTra = debug_backtrace();
			$aTra = array_pop( $aTra); //取最后一条
			$callRes = implode("\n", array(date('Y-m-d H:i:s'), '[mongodb fata] '.$appmsg, json_encode($e), $_SERVER["PHP_SELF"], 'file:'.$aTra['file'], 'line:'.$aTra['line'], 'function:'.$aTra['function'], 'args:'.implode(',', (array)$aTra['args']) ) );
			oo::logs()->debug($callRes, 'mumongo.fatalerr.txt');
			functions::fatalError($callRes );
			var_dump( $e->getMessage());
			die("mongo error");
		}else{
			foreach((array)$e->getTrace() as $i => $row){
				$args = var_export($row['args'], true);
				$callRes .= "[{$row['file']};{$row['line']};{$row['function']};{$args};] \n";
			}
			$this->log($e->getCode(), $e->getMessage(), $appmsg, $callRes);
			if($errLevel == 0){
				$error = $e->getCode() . $e->getMessage() . ' '.  $appmsg . date("H:i:s") . '[mongodb fata]';
				oo::logs()->debug($error, 'mumongo.fatalerr.txt');
				functions::fatalError($error);
				var_dump( $e->getMessage());
				die("mongo error");
			}
		}
	}
	//错误日志
	public function log($syscode, $sysmsg, $appmsg, $callRes){
		$time = date("Ymd H:i:s");
		if($syscode == 11000) return true;
		$error = "{$time};[syscode]:{$syscode};[sysmsg]:{$sysmsg};[appmsg]:{$appmsg};[callRes]:\n{$callRes}";
		oo::logs()->debug($error, 'mumongo.txt');
	}
	public function __destruct(){
		$this->close();

		//$this->isReplicaSet() or $this->close();
	}
	public function activeTime($method, $actime, $length){
		return false;

		if( ! in_array(oo::$config['sid'], array(60))){
			return false;
		}

		$aCorelog = array();
		$aCorelog['m'] = $method;
		$aCorelog['ac'] = $actime;
		$aCorelog['l'] = $length;
		$aCorelog['rep'] = $this->isReplicaSet();
		$log = json_encode($aCorelog);
		oo::logs()->debug($log, 'mumongo.activeTime.txt');
		//oo::golast()->add( 'corelog', $aCorelog, array( 'mid' => 1 ) );
	}
	//$arr  字段名加f
	public function makeValidFields($arr){
		$aTemp = array();
		foreach((array)$arr as $k => $v){
			$aTemp[$k] = 'f' . $v;
		}
		return $aTemp;
	}
	/**
	 * 把对应的值压入数组.此处过滤掉null及空串以节约存储
	 */
	public function mongoCombine( $aKey, $aValue, $strKey = true, $aIntFields = array()){
		$aTemp = array();
		$strKey === true && $aKey = array_flip( $aKey);//如果是字符串键 则对调键值
		foreach( (array) $aValue as $vkey => $value ){
			$nkey = (($strKey === true) and isset($aKey[$vkey])) ? $aKey[$vkey] : $vkey;
			$fkey = 'f' . $nkey;
			in_array($nkey, $aIntFields) && ($value = (int)$value);
			$aTemp[$fkey] = $value;
			//第一个字符为 f
		}
		return (array) $aTemp;
	}

	/**
	 * 反转数组
	 */
	public function mongoUncombine( $aKey, $aValue){
		$aTemp = array();
		foreach( (array) $aValue as $fkey => $value ){
			$key = substr($fkey, 1);//第一个字符为 f
			isset($aKey[$key]) and ($aTemp[$aKey[$key]] = $value);
		}
		return (array) $aTemp;
	}
	
	/**
	 * 类似于mysql里的mysql_query
	 * @example 
	 * $res = ocache::mongoBase()->query($table, $criteria);
	 * while($row = $res->getNext()){
	 *     echo '取得的数据：'.$row;
	 * }
	 * 
	 * @param string $table
	 * @param array $criteria
	 * @param array $fields
	 * @param array $aSort
	 * @param array $aLimit
	 * @return obj
	 */
	public function query($table, $criteria, $fields = array(), $aSort = array(), $aLimit = array()){
		$collObj = $this->selectCollection($table);
		if( ! $collObj){
			return $this->genRet(0, array(), $collObj, $this->err, __line__);
		}
		$aData = array();
		$aFields = array();
		if(is_array($fields) && ! empty($fields)){
			foreach($fields as $f){
				$aFields[$f] = 1;
			}
		}
		//索引检测
		if( ! $this->checkIndex($table, $criteria)){
			//"no index";
			functions::fatalError('[mongodb fata] NO index:' . json_encode(array($table, $criteria)));
		}

		try{
			$retCursor = $collObj->find($criteria, $aFields);
			if( (!empty($aSort) ) && is_array($aSort)){
				foreach($aSort as $k => $v){
					$aSort[$k] = $v > 0 ? 1 : -1;//1升序 -1 降序
				}
				$retCursor = $retCursor->sort( $aSort);
			}
			//跳过或限制返回数量
			if( (!empty($aLimit) ) && is_array($aLimit)){
				list($skip, $limit) = $aLimit;
				if($skip > 0){
					$retCursor = $retCursor->skip( $skip);
				}
				if($limit > 0){
					$retCursor = $retCursor->limit( $limit);
				}
			}
		}catch(MongoConnectionException $e){
			$this->exceptionLog($e, "query");
			$errExcep = $e->getMessage();
		}catch(MongoCursorException $e){
			$this->exceptionLog($e, "query");
			$errExcep = $e->getMessage();
		}catch(Exception $e){
			$this->exceptionLog($e, "query");
			$errExcep = $e->getMessage();
		}
		return $errExcep ? $errExcep : $retCursor;
	}

	/**********下面方法是redis for mongo**************/
	/**
	 * 设置.有则覆盖.true成功false失败
	 * @param String $key
	 * @param Mixed $value
	 * @param int $Timeout 过期时间(秒).最好用setex
	 * @return Boolean
	 */
	public function set($key, $value, $Timeout=0){
		is_numeric($value) && ( $value + 0 !== INF ) && ($value+= 0);//避免字符串中仅包含1个e误认为是科学计数法，如需传无限大的数字，不能传字符串过来
		$Timeout = $Timeout ? (int)$Timeout + time() : $this->expire;
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key), array('$set' => array('v' => $value, 't'=>(int)$Timeout ) ), array('upsert' =>1));
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 设置带过期时间的值
	 * @param String $key
	 * @param Mixed $value
	 * @param int $expire 过期时间(秒).默认24小时
	 * @return Boolean
	 */
	public function setex($key, $value, $expire=86400){
		is_numeric($value) && ($value+= 0);
		$expire = $expire ? (int)$expire + time() : $this->expire;
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key), array('$set' => array('v' => $value, 't'=>(int)$expire ) ), array('upsert' => 1));
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 设置带过期时间的值
	 * @param String $key
	 * @param Mixed $value
	 * @param int $expire 过期时间(微秒).默认24小时
	 * @return Boolean
	 */
	public function psetex($key, $value, $expire=86400000){
		is_numeric($value) && ($value+= 0);
		$expire = $expire/1000;
		$expire = $expire ? (int)$expire + time() : $this->expire;
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key), array('$set' => array('v' => $value, 't'=>(int)$expire ) ), array('upsert' => 1));
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 添加.存在该Key则返回false.
	 * @param String $key
	 * @param Mixed $value
	 * @return Boolean
	 */
	public function setnx($key, $value){
		is_numeric($value) && ($value+= 0);
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key, 'v'=>array('$exists'=>false) ), array('$set' => array('v' => $value, 't'=>$this->expire ) ), array('upsert' => 1, 'safe'=>true));
		return (bool)$aMongoRet['sta'];
	}
	/**
     * 返回某key剩余的时间.单位是秒(只支持str)
     * @param String $key
     * @return int/false -1为没有设置过期时间
     */
    public function ttl($key, $v = 0) {
		$aMongoRet = $this->findOne($this->mgtblactstr, array('_id' => $key), array('t'));
		if (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['t'])) {
			$t = (int)$aMongoRet['data']['t'];
			if($v) return $t;
			return max($t - time(), -1);
		}
		return -1;
	}
	/**
     * 返回某key剩余的时间.单位是微秒(只支持str)
     * @param String $key
     * @return int/false -1为没有设置过期时间
     */
    public function pttl($key) {
		$aMongoRet = $this->findOne($this->mgtblactstr, array('_id' => $key), array('t'));
		if (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['t'])) {
			$t = (int) $aMongoRet['data']['t'] * 1000;
			$mtime = round(microtime(true) * 1000);
			return max($t - (int)$mtime, -1);
		}
		return -1;
	}

	/**
	 * 添加.存在该Key则返回false.
	 * @param String $key
	 * @param Mixed $value
	 * @return Boolean
	 */
	public function add($key, $value, $expire=86400){
		is_numeric($value) && ($value+= 0);
		$expire = $expire ? (int)$expire + time() : $this->expire;
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key, '$or' =>array(array('v'=>array('$exists'=>false)),array('t' => array('$lt' => time())) )), array('$set' => array('v' => $value, 't'=>$expire) ), array('upsert' => 1, 'safe'=>true));
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 原子递增1.不存在该key则基数为0.注意因为serialize的关系不能在set方法的key上再执行此方法
	 * @param String $key
	 * @return false/int 返回最新的值
	 */
	public function incr( $key, $expire=0){
		return $this->incrBy($key, 1, $expire);
	}
	/**
	 * 原子递加指定的整数.不存在该key则基数为0,注意$value可以为负数.返回的结果也可能是负数
	 * !!!如果超过42亿,请用incrByFloat
	 * @param String $key
	 * @param int $value 可以为0 负数
	 * @return false/int 返回最新的值
	 */
	public function incrBy($key, $value, $expire=0){
		$expire = $expire ? (int)$expire + time() : $this->expire;
		$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$inc' => array('v' => (int)$value ), '$set' => array('t'=>$expire ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		if($aMongoRet['err'] && strpos($aMongoRet['err'],'Cannot apply $inc modifier to non-number') ){
			$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$unset' => array('v' => (int)$value ) ),
													array('v'),
													array('upsert' => 1));
			$oldvalue = ($aMongoRet['sta'] == 1) ? (int)$aMongoRet['data']['v'] : 0;
			$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$inc' => array('v' => (int)$value+$oldvalue ), '$set' => array('t'=>$expire ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		}
		return ($aMongoRet['sta'] == 1) ? (int)$aMongoRet['data']['v'] : false;
	}
	/**
	 * 原子递加指定的浮点数.不存在该key则基数为0,注意$value可以为负数.返回的结果也可能是负数
	 * @param String $key
	 * @param Float $value 可以为0
	 * @return false/float 返回最新的值
	 */
	public function incrByFloat($key, $value, $expire=0){
		$expire = $expire ? (int)$expire + time() : $this->expire;
		$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$inc' => array('v' => (float)$value ), '$set' => array('t'=>$expire ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		if($aMongoRet['err'] && strpos($aMongoRet['err'],'Cannot apply $inc modifier to non-number') ){
			$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$unset' => array('v' => (float)$value ) ),
													array('v'),
													array('upsert' => 1));
			$oldvalue = ($aMongoRet['sta'] == 1) ? (float)$aMongoRet['data']['v'] : 0;
			$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$inc' => array('v' => (float)$value+$oldvalue ), '$set' => array('t'=>$expire ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		}
		return ($aMongoRet['sta'] == 1) ? (float)$aMongoRet['data']['v'] : false;
	}
	/**
	 * 原子递减1.不存在该key则基数为0.可以减成负数
	 * @param String $key
	 * @return false/int 返回最新的值
	 */
    public function decr( $key, $expire=0){
    	return $this->incrBy($key, -1, $expire);
    }
    /**
	 * 原子递减指定的数.不存在该key则基数为0,注意$value可以是负数(负负得正就成递增了).可以减成负数
	 * !!!如果超过42亿,请用incrByFloat的负数形式
	 * @param String $key
	 * @param int $value
	 * @return false/int 返回最新的值
	 */
    public function decrBy($key, $value, $expire=0){
		return $this->incrBy($key, -$value, $expire);
	}
	/**
	 * 获取.不存在则返回false
	 * @param String $key
	 * @return false/Mixed
	 */
	public function get( $key){
		$aMongoRet = $this->findOne($this->mgtblactstr, array('_id' => $key, 't'=>array('$gte'=>time() ) ), array('v'));
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['v'])) ? $aMongoRet['data']['v'] : false;
	}
	/**
     * 先获取该key的值,然后以新值替换掉该key.该key不存在则添加同时返回false
     * @param String $key
     * @param Mixed $value
     * @return Mixed/false
     */
    public function getSet($key, $value){
    	is_numeric($value) && ($value+= 0);
   		$aMongoRet = $this->findAndModify($this->mgtblactstr,
													array('_id' => $key),
													array('$set' => array('v' => $value, 't'=>$this->expire ) ),
													array('v'),
													array('upsert' => 1));
		return ($aMongoRet['sta'] == 1) ? $aMongoRet['data']['v'] : false;
    }
   /**
     * 重命名某个Key.注意如果目的key存在将会被覆盖
     * @param String $srcKey
     * @param String $dstKey
     * @return Boolean 源key和目的key相同或者源key不存在...
     */
    public function renameKey($srcKey, $dstKey) {
		$flag = false;
		($res = $this->get($srcKey)) && $this->set($dstKey, $res) && $flag = true;
		$res = $this->lGetRange($srcKey, 0, -1);
		$inserData = array();
		$i = 0;
		$collObj = $this->selectCollection($this->mgtblactlist);
		foreach ((array) $res as $v) {
			is_numeric($v) && ($v += 0);
			$_id = functions::creatUuid('L');
			$inserData[] = array('_id' => (int) $_id, 'k' => $dstKey, 'v' => $v, 't' => time());
			if (++$i % 300 === 0) {
				$collObj->batchInsert($inserData, array('continueOnError' => true));
				$inserData = array();
			}
			$flag = true;
		}
		if (!empty($inserData)) {
			$collObj->batchInsert($inserData, array('continueOnError' => true));
			$inserData = array();
		}
		$flag && $this->delete($srcKey, true);
		return $flag;
	}

	/**
     * 重命名某个Key.和renameKey不同: 如果目的key存在将不执行
     * @param String $srcKey
     * @param String $dstKey
     * @return Boolean 源key和目的key相同或者源key不存在或者目的key存在
     */
    public function renameNx($srcKey, $dstKey) {
		$flag = false;
		($res = $this->get($srcKey)) && $this->setnx($dstKey, $res) && $flag = true;
		if ($this->lGetRange($dstKey, 0, -1)) { //已存在
			return $flag;
		}
		$inserData = array();
		$i = 0;
		$res = $this->lGetRange($srcKey, 0, -1);
		$collObj = $this->selectCollection($this->mgtblactlist);
		foreach ((array) $res as $v) {
			is_numeric($v) && ($v += 0);
			$_id = functions::creatUuid('L');
			$inserData[] = array('_id' => (int) $_id, 'k' => $dstKey, 'v' => $v, 't' => time());
			if (++$i % 300 === 0) {
				$collObj->batchInsert($inserData, array('continueOnError' => true));
				$inserData = array();
			}
			$flag = true;
		}
		if (!empty($inserData)) {
			$collObj->batchInsert($inserData, array('continueOnError' => true));
			$inserData = array();
		}
		$flag && $this->delete($srcKey, true);
		return $flag;
	}

	/**
     * 设置某个key过期时间(Time To Live)expire. (redis2.1.3前的版本只能设置一次）
     * @param String $key
     * @param int $ttl 存活时长(秒)
     * @return Boolean $key不存在为false
     */
    public function expire($key, $ttl){
    	$expire = (int)$ttl + time();
		return $this->expireAt($key, (int)$expire);
    }
    /**
     * 设置某个key过期时间(Time To Live)expire. (redis2.1.3前的版本只能设置一次）
     * @param String $key
     * @param int $ttl 存活时长(微秒)
     * @return Boolean $key不存在为false
     */
    public function pexpire($key, $ttl){
    	$expire = (int)$ttl/1000 + time();
		return $this->expireAt($key, (int)$expire);
    }
    /**
     * 设置某个key在特定的时间过期.如 strtotime('2014-11-11 11:11:11')
     * @param String $key
     * @param int $timestamp 时间戳(秒)
     * @return Boolean
     */
    public function expireAt($key, $timestamp){
		$aMongoRet = $this->update($this->mgtblactstr, array('_id' => $key), array('$set' => array('t'=>(int)$timestamp ) ), array('upsert' => 1));
		return (bool)$aMongoRet['sta'];
    }
    /**
     * 设置某个key在特定的时间过期
     * @param String $key
     * @param int $timestamp 时间戳(微秒)
     * @return Boolean
     */
    public function pexpireAt($key, $timestamp){
		return $this->expireAt($key, (int)$timestamp/1000);
    }
	/**
	 * 批量获取.注意: 如果某键不存在则对应的值为false
	 * @param Array $keys
	 * @return Array 原顺序返回
	 */
	public function getMultiple( $keys){
		$aMongoRet = $this->find($this->mgtblactstr, array('_id' => array('$in'=>$keys), 't'=>array('$gte'=>time() ) ) );
		$res = array();
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach($aMongoRet['data'] as $v){
				$res[$v['_id']] = $v['v'];
			}
		}
		return $res;
	}

	/**
	 * 兼容memcache的getMulti写法
	 */
	public function getMulti( $keys){
		return $this->getMultiple($keys);
	}

	/**
	 * List章节 无索引序列 把元素加入到队列左边(头部).如果不存在则创建一个队列.返回该队列当前元素个数/false
	 * 注意对值的匹配要考虑到serialize.array(1,2)和array(2,1)是不同的值
	 * @param String $key
	 * @param Mixed $value
	 * @return false/Int. 如果连接不上或者该key已经存在且不是一个队列
	 */
	public function lPush($key, $value){
		is_numeric($value) && ($value+= 0);
		$_id = functions::creatUuid('L');
		$aMongoRet = $this->insert($this->mgtblactlist, array('_id'=>(int)$_id, 'k'=>$key, 'v'=>$value, 't'=>time() ) );
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 把元素加入到队列右边(尾部).如果不存在则创建一个队列.返回该队列当前元素个数/false
	 * @param String $key
	 * @param Mixed $value
	 * @return false/int 如果连接不上或者该key已经存在且不是一个队列
	 */
	public function rPush($key, $value){
		is_numeric($value) && ($value+= 0);
		$_id = functions::creatUuid('L');
		$aMongoRet = $this->insert($this->mgtblactlist, array('_id'=>(int)$_id, 'k'=>$key, 'v'=>$value, 't'=>1 ) );
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 弹出(返回并清除)队列头部(最左边)元素
	 * @param String $key
	 * @return Mixed/falsew
	 */
	public function lPop( $key){
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array(), array('t'=>-1), array(0, 1) );
		$res = (bool)$aMongoRet['sta'];
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			$res = $aMongoRet['data'][0]['v'];
			($_id = (int)$aMongoRet['data'][0]['_id']) && $this->remove($this->mgtblactlist, array('_id' =>(int)$_id));
		}
		return $res;
	}
	/**
	 * 弹出队列尾部(最右边)元素
	 * @param String $key
	 * @return Mixed/false
	 */
	public function rPop( $key){
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array(), array('t'=>1), array(0, 1) );
		$res = (bool)$aMongoRet['sta'];
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			$res = $aMongoRet['data'][0]['v'];
			($_id = (int)$aMongoRet['data'][0]['_id']) && $this->remove($this->mgtblactlist, array('_id'=>(int)$_id));
		}
		return $res;
	}
	/**
	 * 返回队列里的元素个数.不存在则为0.不是队列则为false
	 * @param String $key
	 * @return int/false
	 */
	public function lSize( $key){
		$aMongoRet = $this->count($this->mgtblactlist, array('k' => $key) );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ? (int)$aMongoRet['data'] : false;
	}
	/**
	 * 控制队列只保存某部分listTrim,即:删除队列的其余部分
	 * @param String $key
	 * @param int $start
	 * @param int $end
	 * @return Boolean 不是一个队列或者不存在...
	 */
	public function lTrim($key, $start, $end){

		$aNeedDelete = $aDeleteRet1 = $aDeleteRet2 = array();
		$iTotal = 0;
		$res = false;

		$aTemp = $this->count($this->mgtblactlist, array('k' => $key));
		$iTotal = $aTemp['data'];

		if($start < 0 || $end < 0){
		    $start = $start >= 0 ? $start : $iTotal - abs($start);
		    $end = $end >= 0 ? $end : $iTotal - abs($end);

		    if($start > $end){
				$tmp = $end;
				$end = $start;
				$start = $tmp;
		    }
		}

		if($start > 0){
		    $aDeleteRet1 = $this->find($this->mgtblactlist, array('k' => $key),array('_id'),array('t'=> -1 ),array(0, $start));
		}


		$aDeleteRet2 = $this->find($this->mgtblactlist, array('k' => $key),array('_id'),array('t'=> -1 ),array($end + 1,$iTotal));


		if($aDeleteRet1['sta'] == 1 && $aDeleteRet2['sta'] == 1){
		    $aNeedDelete = array_merge($aDeleteRet1['data'],$aDeleteRet2['data']);
		}elseif(empty($aDeleteRet1) && $aDeleteRet2['sta'] == 1){
		    $aNeedDelete = $aDeleteRet2['data'];
		}


		if(!empty($aNeedDelete)){
			foreach ((array)$aNeedDelete as $v){
				($_id = (int)$v['_id']) && $this->remove($this->mgtblactlist, array('_id'=>(int)$_id));
			}
			$res = true;
		}

		return $res;


		/*$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array(), array('t'=>1), array($start, $end-$start) );
		$res = (bool)$aMongoRet['sta'];
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach ((array)$aMongoRet['data'] as $v){
				($_id = (int)$v['_id']) && $this->remove($this->mgtblactlist, array('_id'=>(int)$_id));
			}
			$res = true;
		}
		return $res;*/
	}
	/**
	 * 设置的队列中索引的新值
	 * @param string $key
	 * @param int $index
	 * @param mixed $value
	 * @return boolean
	 */
	public function lSet($key, $index, $value) {
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array('_id'), array('t' => -1), array($index, 1));
		if (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'][0])) {
			$id = (int) $aMongoRet['data'][0]['_id'];
			$aMongoRet = $this->update($this->mgtblactlist, array('_id' => $id), array('$set' => array('v' => $value)));
			return $aMongoRet['sta'] && $aMongoRet['resRet']['ok'];
		}
		return false;
	}

	/**
	 * 获取队列中索引的新值
	 * @param string $key
	 * @param int $index
	 * @return boolean
	 */
	public function lGet($key, $index) {
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array('v'), array('t' => -1), array($index, 1));
		if (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'][0])) {
			return $aMongoRet['data'][0]['v'];
		}
		return false;
	}

	/**
	 * 取出队列的某一段.不存在则返回空数组(lpush进去的)
	 * @param String $key
	 * @param String $start 相当于$index:第一个为0...最后一个为-1
	 * @param String $end
	 * @return Array
	 */
	public function lGetRange($key, $start, $end){
		$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, $end-$start) );
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array(), array('t'=>-1), $aLimit );
		$res = array();
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach($aMongoRet['data'] as $v){
				$res[] = $v['v'];
			}
		}
		return $res;
	}
	/**
	 * 取出队列的某一段.不存在则返回空数组(rpush进去的)
	 * @param String $key
	 * @param String $start 相当于$index:第一个为0...最后一个为-1
	 * @param String $end
	 * @return Array
	 */
	public function rGetRange($key, $start, $end){
		$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, $end-$start) );
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key), array(), array('t'=>1), $aLimit );
		$res = array();
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach($aMongoRet['data'] as $v){
				$res[] = $v['v'];
			}
		}
		return $res;
	}
	/**
	 * 删掉队列中的某些值
	 * @param String $key
	 * @param Mixed $value 要删除的值.可以是复杂数据,但要考虑serialize
	 * @param int $count 去掉的个数,>0从左到右去除;<0从右到左去除
	 * @return int 删除的个数
	 */
	public function lRemove($key, $value, $count=1) {
		$aMongoRet = $this->find($this->mgtblactlist, array('k' => $key, 'v' => $value), array(), array('t' => $count > 0 ? -1 : 1), array(0, abs($count)));
		$res = 0;
		if (isset($aMongoRet['data']) && is_array($aMongoRet['data'])) {
			foreach ($aMongoRet['data'] as $data) {
				if ($_id = (int) $data['_id']) {
					$aMongoTmp = $this->remove($this->mgtblactlist, array('_id' => (int) $_id));
					($aMongoTmp['sta'] == 1) && ($res += 1);
				}
			}
		}
		return $res;
	}
	/**
	 * 给该key添加一个唯一值.相当于制作一个没有重复值的数组
	 * @param String $key
	 * @param Mixed $value
	 * @return false/int 该值存在或者该键不是一个集合返回0,连接失败为false,否则为添加成功的个数1
	 */
	 public function sAdd($key, $value){
	 	is_numeric($value) && ($value+= 0);
	 	$aMongoRet = $this->insert($this->mgtblactset, array('_id'=>$key.'_'.$value, 'k'=>$key, 'v'=>$value ), true );
		return (bool)$aMongoRet['sta'];
	 }
	/**
	 * 获取某key对象个数  scard
	 * @param String $key
	 * @return int 不存在则为0
	 */
    public function sSize( $key){
    	$aMongoRet = $this->count($this->mgtblactset, array('k' => $key) );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ? (int)$aMongoRet['data'] : 0;
    }
    /**
     * 删除该集合中对应的值
     * @param String $key
     * @param String $value
	 * @return Boolean 没有该值返回false
	 */
    public function sRemove($key, $value){
    	is_numeric($value) && ($value+= 0);
    	$aMongoRet = $this->remove($this->mgtblactset, array('_id' => $key.'_'.$value) );
		return (bool)$aMongoRet['sta'];
    }
    /**
     * 判断该数组中是否有对应的值
     * @param String $key
     * @param String $value
	 * @return Boolean 集合不存在或者值不存在->false
	 */
    public function sContains($key, $value){
    	is_numeric($value) && ($value+= 0);
    	$aMongoRet = $this->findOne($this->mgtblactset, array('_id' => $key.'_'.$value) );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['v'])) ? true : false;
    }
    /**
     * 获取某数组所有值sGetMembers
     * @param String $key
	 * @return Array 顺序是不固定的
	 */
    public function sMembers( $key){
    	$aMongoRet = $this->find($this->mgtblactset, array('k' => $key), array('v') );
   		$res = array();
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach($aMongoRet['data'] as $v){
				$res[] = $v['v'];
			}
		}
		return $res;
    }

	 /**
     * 随机弹出一个值.
     * @param String $key
     * @return Mixed/false 没有值了或者不是一个集合
     */
    public function sPop($key){
		$aMongoRet = $this->find($this->mgtblactset, array('k' => $key) );
   		$res = array();
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			$count = $aMongoRet['data'] ? count($aMongoRet['data']) -1 : 0;
			$data = $aMongoRet['data'][mt_rand(0, $count)];
			$res = $data['v'];
		}
		if(!$res){
			return false;
		}
		$this->sRemove($key, $res);
		return $res;
	}

	/**
	 * 删除整个集合的值
	 * @param type $key
	 */
	public function sDel($key){
		$aMongoRet = $this->remove($this->mgtblactset, array('k' => $key) );
		return (bool)$aMongoRet['sta'];
	}

	/**
     * 有序集合.添加一个指定了索引值的元素(默认索引值为0).元素在集合中存在则更新对应$score
     * @param String $key
     * @param int $score 索引值
     * @param Mixed $value 注意考虑到默认使用了序列化,此处最好强制数据类型
     * @return false/int 成功加入的个数
     */
    public function zAdd($key, $score, $value){
    	is_numeric($score) && ($score += 0);
		$aMongoRet = $this->update($this->mgtblactSortedSet, array('_id' =>$key.'_'.$value), array('$set' => array('k'=>$key, 'v' => $value, 's'=>$score ) ), array('upsert' =>1));
		return (bool)$aMongoRet['sta'];
    }
    /**
     * 获取指定单元的数据
     * @param String $key
     * @param int $start 起始位置,从0开始
     * @param int $end 结束位置,-1结束
     * @param Boolean $withscores 是否返回索引值.如果是则返回[值=>索引]的数组.如果要返回索引值,存入的时候$value必须是标量
     * @return Array
     */
    public function zRange($key, $start, $end, $withscores=false, $filter = 0){
    	$fields = ($withscores===true) ? array() : array('v');
    	$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, ($end+1)-$start) );
    	$criteria = array('k' => $key );
    	( (int)$filter !== 0 ) && ( $criteria['s'] = array('$gte'=>(int)$filter) );
    	$aMongoRet = $this->find($this->mgtblactSortedSet, $criteria, $fields, array('s'=>1), $aLimit );
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach((array)$aMongoRet['data'] as $v){
				if($withscores===true){
					$res[$v['v']] = $v['s'];
				}else{
					$res[] = $v['v'];
				}
			}
		}
		return (array)$res;
    }
    /**
     * 获取指定单元的反序排列的数据
     * @param String $key
     * @param int $start
     * @param int $end
     * @param Boolean $withscores 是否返回索引值.如果是则返回值=>索引的数组
     * @return Array
     */
    public function zRevRange($key, $start, $end, $withscores=false, $filter = 0){
    	$fields = ($withscores===true) ? array() : array('v');
    	$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, ($end+1)-$start) );
    	$criteria = array('k' => $key );
    	( (int)$filter !== 0 ) && ( $criteria['s'] = array('$gte'=>(int)$filter) );
    	$aMongoRet = $this->find($this->mgtblactSortedSet, $criteria, $fields, array('s'=>-1), $aLimit );
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach((array)$aMongoRet['data'] as $v){
				if($withscores===true){
					$res[$v['v']] = $v['s'];
				}else{
					$res[] = $v['v'];
				}
			}
		}
		return (array)$res;
	}
	/**
	 * 获取指定条件下的集合
	 * @param String $key
	 * @param int $start 最小索引值
	 * @param int $end 最大索引值
	 * @param Array $options array('withscores'=>true,limit=>array($offset, $count))
	 * @return Array
	 */
    public function zRangeByScore($key, $start, $end, $options=array()){
    	$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, ($end+1)-$start) );
    	$aMongoRet = $this->find($this->mgtblactSortedSet, array('k' => $key, 's' => array('$gte' => $start, '$lte' => $end) ), array('s','v'), array('v'=>1), $aLimit );
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach((array)$aMongoRet['data'] as $v){
				if($options['withscores']===true){
					$res[$v['v']] = $v['s'];
				}else{
					$res[] = $v['v'];
				}
			}
		}
		return (array)$res;
    }
    /**
	 * 获取指定条件下的反序排列集合
	 * @param String $key
	 * @param int $start 最大索引值
	 * @param int $end 最小索引值
	 * @param Array $options array('withscores'=>true,limit=>array($offset, $count))
	 * @return Array
	 */
    public function zRevRangeByScore($key, $start, $end, $options=array()){
    	$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, ($end+1)-$start) );
    	$aMongoRet = $this->find($this->mgtblactSortedSet, array('k' => $key, 's' => array('$gte' => $start, '$lte' => $end) ), array('s','v'), array('v'=>-1), $aLimit );
		if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
			foreach((array)$aMongoRet['data'] as $v){
				if($options['withscores']===true){
					$res[$v['v']] = $v['s'];
				}else{
					$res[] = $v['v'];
				}
			}
		}
		return (array)$res;
	}
	/**
	 * 返回指定索引值区域内的元素个数
	 * @param String $key
	 * @param int/String $start 最小索引值 前面加左括号表示不包括本身如: '(3' 表示>3而不是默认的>=3
	 * @param int/String $end 最大索引值 '(4'表示...
	 * @return int
	 */
    public function zCount($key, $start, $end){
    	$aLimit = (($start == 0) && ($end == -1)) ? array() : ((($start > 0) && ($end == -1)) ? array($start, 1000) : array($start, $end-$start) );
    	$aMongoRet = $this->find($this->mgtblactSortedSet, array('k' => $key ), array(), array(), $aLimit );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ? count($aMongoRet['data']) : 0;
    }
    /**
     * 删除指定索引值区域内的所有元素zRemRangeByScore
     * @param String $key
     * @param int $start 最小索引值
     * @param int $end 最大索引值
     * @return int 返回删除的个数
     */
    public function zDeleteRangeByScore($key, $start, $end){
		$keys = range($start, $end);
		$aMongoRet = $this->remove($this->mgtblactSortedSet, array('k' => $key, 's' => array('$in'=>$keys)), false, true );
		return isset($aMongoRet['resRet']['n']) ? (int)$aMongoRet['resRet']['n'] : 0;
    }
    /**
     * 删除指定排序范围内的所有元素
     * @param int $start 排序起始值
     * @param int $end
     * @return int 返回删除的个数
     */
    public function zDeleteRangeByRank($key, $start, $end){
		$aMinRet = $this->find($this->mgtblactSortedSet, array('k' => $key), array('s'), array('s' => ($start < 0) ? -1 : 1), array(max(0, abs($start) - 1), 1));
		$min = (($aMinRet['sta'] == 1) && !empty($aMinRet['data'][0])) ? (int)$aMinRet['data'][0]['s'] : 0;
		$aMaxRet = $this->find($this->mgtblactSortedSet, array('k' => $key), array('s'), array('s' => ($end < 0) ? -1 : 1), array(max(0, abs($end) - 1), 1));
		$max = (($aMaxRet['sta'] == 1) && !empty($aMaxRet['data'][0])) ? (int)$aMaxRet['data'][0]['s'] : 0;
		$aMongoRet = $this->remove($this->mgtblactSortedSet, array('k' => $key, 's' => array('$gte' => $min, '$lte' => $max)), false, true );
		return isset($aMongoRet['resRet']['n']) ? (int)$aMongoRet['resRet']['n'] : 0;
	}
	/**
	 * 获取集合元素个数zCard
	 * @param String $key
	 * @return int
	 */
    public function zSize( $key){
		return $this->getCount($this->mgtblactSortedSet, array('k' => $key ));
    }
    /**
     * 获取某集合中某元素的索引值
     * @param String $key
     * @param String $member
     * @return int/false 没有该值为false
     */
    public function zScore($key, $member){
    	$aMongoRet = $this->findOne($this->mgtblactSortedSet, array('_id' =>$key.'_'.$member ));
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['s'])) ? $aMongoRet['data']['s']+0 : false;
    }
    /**
     * 获取指定元素的排序值
     * @param String $key
     * @param String $member
     * @return int/false 不存在为false
     */
    public function zRank($key, $member, $s = null) {
		$score = ($s === null) ? $this->zScore($key, $member) : (int)$s;
		if (!$score) {
			return false;
		}
		return $this->getCount($this->mgtblactSortedSet, array('s' => array('$lt' => $score), 'k' => $key));
	}

	/**
     * 获取指定元素的反向排序值
     * @param String $key
     * @param String $member
     * @return int/false 不存在为false
     */
    public function zRevRank($key, $member, $s = null) {
		$score = ($s === null) ? $this->zScore($key, $member) : (int)$s;
		if (!$score) {
			return false;
		}
		return $this->getCount($this->mgtblactSortedSet, array('s' => array('$gt' => $score), 'k' => $key));
	}

	/**
     * 给指定的元素累加索引值.元素不存在则会被添加
     * @param String $key
     * @param int $value 要加的索引值量
     * @param String $member
     * @return int 该元素最新的索引值
     */
    public function zIncrBy($key, $value, $member){
		is_numeric($value) && ($value += 0);
    	$aMongoRet = $this->findAndModify($this->mgtblactSortedSet,
													array('_id' => $key.'_'.$member),
													array('$inc' => array('s' => $value ), '$set' => array('k' => $key, 'v' => $member) ),
													array('s'),
													array('upsert' => 1, 'new'=>true));
    	if($aMongoRet['err'] && strpos($aMongoRet['err'],'Cannot apply $inc modifier to non-number') ){
			$aMongoRet = $this->findAndModify($this->mgtblactSortedSet,
													array('_id' => $key.'_'.$member),
													array('$unset' => array('s' => $value ) ),
													array('s'),
													array('upsert' => 1));
			$oldvalue = ($aMongoRet['sta'] == 1) ? ($aMongoRet['data']['s']+0) : 0;
			$aMongoRet = $this->findAndModify($this->mgtblactSortedSet,
													array('_id' => $key.'_'.$member),
													array('$inc' => array('s' => $value + $oldvalue ), '$set' => array('k' => $key, 'v' => $member) ),
													array('s'),
													array('upsert' => 1, 'new'=>true));
		}
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['s'])) ? $aMongoRet['data']['s'] : false;
    }
    /**
     * 得到一个并集存储到新的集合中
     * @param String $keyOutput 新集合名
     * @param Array $arrayZSetKeys 需要合并的集合 array('key1', 'key2')
     * @param Array $arrayWeights 对应集合中索引值要放大的倍数  array(5, 2)表示第一个集合中的索引值*5,第二个集合中的索引值*2,然后再合并
     * @param String $aggregateFunction 如果有相同元素,则取索引值的方法: "SUM", "MIN", "MAX"
     * @return int 新集合的元素个数
     */
    public function zUnion($keyOutput, $arrayZSetKeys, $arrayWeights, $aggregateFunction){
    }
    /**
     * 得到一个交集存储到新的集合中
     * @param String $keyOutput 新集合名
     * @param Array $arrayZSetKeys 需要合并的集合 array('key1', 'key2')
     * @param Array $arrayWeights 对应集合中索引值要放大的倍数  array(5, 2)表示第一个集合中的索引值*5,第二个集合中的索引值*2,然后再合并
     * @param String $aggregateFunction 如果有相同元素,则取索引值的方法: "SUM", "MIN", "MAX"
     * @return int 新集合的元素个数
     */
    public function zInter($keyOutput, $arrayZSetKeys, $arrayWeights, $aggregateFunction){
    }
    /**
     * 删除对应的值zRem
     * @param String $key
     * @param Mixed $member
     * @return Boolean true 成功,false 失败
     */
    public function zDelete($key, $member){
		$aMongoRet = $this->remove($this->mgtblactSortedSet, array('_id' => $key.'_'.$member ), true, true );
		return isset($aMongoRet['resRet']['n']) ? (bool)$aMongoRet['resRet']['n'] : false;
    }

	/**
	 * 删除整个有序集合
	 * @param type $key
	 * @return type
	 */
	public function zDel($key){
		$aMongoRet = $this->remove($this->mgtblactSortedSet, array('k'=>$key));
		return (bool)$aMongoRet['sta'];
	}

    /**
     * 设置或替换Hash.
     * @param String $key
     * @param String $hashKey
     * @param Mixed $value
     * @return Boolean
     */
    public function hSet($key, $hashKey, $value){
    	is_numeric($value) && ($value+= 0);
    	$aMongoRet = $this->update($this->mgtblacthashs, array('_id' => $key.'_'.$hashKey), array('$set' => array('k'=>(string)$key, 'hk'=>(string)$hashKey, 'v' => $value ) ) );
		return (bool)$aMongoRet['sta'];
    }
    /**
     * 添加式
     * @param String $key
     * @param String $hashKey
     * @param Mixed $value
     * @return Boolean
     */
    public function hSetNx($key, $hashKey, $value){
    	is_numeric($value) && ($value+= 0);
    	$aMongoRet = $this->insert($this->mgtblacthashs, array('_id' => $key.'_'.$hashKey, 'k'=>(string)$key, 'hk'=>(string)$hashKey, 'v' => $value ), true );
		return (bool)$aMongoRet['sta'];
	}
	/**
	 * 获取单个.失败或不存在为false
	 * @param String $key
	 * @param String $hashKey
	 * @return Mixed
	 */
    public function hGet($key, $hashKey){
    	$aMongoRet = $this->findOne($this->mgtblacthashs, array('_id' => $key.'_'.$hashKey), array('v') );
    	return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['v'])) ? $aMongoRet['data']['v'] : false;
    }
    /**
     * 该Key上Hash数量
     * @param String $key
     * @return int
     */
    public function hLen( $key){
    	$aMongoRet = $this->count($this->mgtblacthashs, array('k' => (string)$key) );
    	return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ? $aMongoRet['data'] : 0;
    }
    /**
     * 删除.成功为true,否则false
	 * @param String $hashKey 大hash Key
     * @param String $key
     * @return Boolean
     */
    public function hDel($key, $hashKey ){
    	$aMongoRet = $this->remove($this->mgtblacthashs, array('_id' => $key.'_'.$hashKey ) );
		return (bool)$aMongoRet['sta'];
    }

	/**
	 * 删除整个hx大key的值
	 * @param type $key
	 */
	public function hDelete($key){
		$akeys = $this->hKeys($key);//获取到所有的key
		if(!$akeys || !is_array($akeys)){
			return 0;
		}
		$n = 0;
		foreach($akeys as $hashKey){
			$this->hDel($key, $hashKey);
			$n++;
		}
		return $n;
	}

    /**
     * 获取所有Key.不存在则为空数组
     * @param String $key
     * @return Array
     */
    public function hKeys( $key){
    	$aMongoRet = $this->find($this->mgtblacthashs, array('k' => (string)$key), array('hk') );
    	$res = array();
    	if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
    		foreach((array)$aMongoRet['data'] as $v){
    			$res[] = $v['hk'];
    		}
    	}
    	return $res;
    }
    /**
     * 获取所有值.不存在则为空数组
     * @param String $key
     * @return Array
     */
    public function hVals( $key){
    	$aMongoRet = $this->find($this->mgtblacthashs, array('k' => (string)$key), array('v') );
    	$res = array();
    	if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
    		foreach((array)$aMongoRet['data'] as $v){
    			$res[] = $v['v'];
    		}
    	}
    	return $res;
    }
    /**
     * 获取所有键值对
     * @param String $key
     * @return Array
     */
    public function hGetAll( $key){
    	$aMongoRet = $this->find($this->mgtblacthashs, array('k' => (string)$key), array('hk','v') );
    	$res = array();
    	if(($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])){
    		foreach((array)$aMongoRet['data'] as $v){
    			$res[$v['hk']] = $v['v'];
    		}
    	}
    	return $res;
    }
    /**
     * 判断$memberKey是否存在
     * @param String $key
     * @param String $memberKey
     * @return Boolean
     */
    public function hExists($key, $hashKey){
    	$aMongoRet = $this->findOne($this->mgtblacthashs, array('_id' => $key.'_'.$hashKey), array('v') );
    	return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['v'])) ? true : false;
    }
    /**
     * 累加减操作.可以减为负数.如果初始值不是整型或者$value不是整型则为false
     * 注意: 因为默认启用了序列化,只能通过此方法设置的$key上做此操作!!!
     * @param String $key
     * @param String $hashKey
     * @param int $value 负数则为减
     * @return int/false 最新的值
     */
    public function hIncrBy($key, $hashKey, $value){
    	$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$inc' => array('v'=>(int)$value), '$set' => array('k'=>(string)$key, 'hk'=>(string)$hashKey ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		if($aMongoRet['err'] && strpos($aMongoRet['err'],'Cannot apply $inc modifier to non-number') ){
			$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$unset' => array('v' => (int)$value ) ),
													array('v'),
													array('upsert' => 1));
			$oldvalue = ($aMongoRet['sta'] == 1) ? (int)$aMongoRet['data']['v'] : 0;
			$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$inc' => array('v' => (int)$value+$oldvalue ), '$set' => array('k'=>(string)$key, 'hk'=>(string)$hashKey ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		}
		return ($aMongoRet['sta'] == 1) ? (int)$aMongoRet['data']['v'] : false;
    }
	/**
     * 累加减操作.可以减为负数.如果初始值不是数值或者$value不是数值则为false
     * 注意: 因为默认启用了序列化,只能通过此方法设置的$key上做此操作!!!
     * @param String $key
     * @param String $hashKey
     * @param float $value 负数则为减
     * @return float/false 最新的值
     */
    public function hIncrByFloat($key, $hashKey, $value){
    	$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$inc' => array('v'=>(float)$value), '$set' => array('k'=>(string)$key, 'hk'=>(string)$hashKey ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		if($aMongoRet['err'] && strpos($aMongoRet['err'],'Cannot apply $inc modifier to non-number') ){
			$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$unset' => array('v' => (float)$value ) ),
													array('v'));
			$oldvalue = ($aMongoRet['sta'] == 1) ? (float)$aMongoRet['data']['v'] : 0;
			$aMongoRet = $this->findAndModify($this->mgtblacthashs,
													array('_id' => $key.'_'.$hashKey),
													array('$inc' => array('v' => (float)$value+$oldvalue ), '$set' => array('k'=>(string)$key, 'hk'=>(string)$hashKey ) ),
													array('v'),
													array('upsert' => 1, 'new'=>true));
		}
		return ($aMongoRet['sta'] == 1) ? $aMongoRet['data']['v'] + 0 : false;
    }
    /**
     * 批量获取.key不存在的对应的值为false
     * @param String $key
     * @param Array $memberKeys
     * @return Array
     */
    public function hMget($key, $memberKeys){
    	$aRet = array();
    	foreach((array)$memberKeys as $k=>$hashKey){
    		$memberKeys[$k] = $key.'_'.$hashKey;
    		$aRet[$hashKey] = false;
    	}
    	$aMongoRet = $this->find($this->mgtblacthashs, array('_id'=>array('$in'=> $memberKeys) ), array('hk', 'v') );
		$res = ($aMongoRet['sta'] == 1) && isset($aMongoRet['data']) ? (array)$aMongoRet['data'] : array();

    	foreach((array)$res as $v){
    		$aRet[$v['hk']] = $v['v'];
    	}

		return $aRet;
    }
    /**
     * 批量设置
     * @param String $key
     * @param Array $members 键值对
     * @return Boolean
     */
    public function hMset($key, $members){
    	foreach((array)$members as $hashKey=>$value){
    		$aMongoRet = $this->hSet($key, $hashKey, $value);
    	}
		return (bool)$aMongoRet;
    }
    /**
     * 批量设置
     * @param Array $pairs 索引数组,索引为key,值为...
     * @return Boolean
     */
    public function mset( $pairs, $expire = 0){
    	foreach((array)$pairs as $key=>$value){
    		is_numeric($value) && ($value+= 0);
    		$res = $this->set($key, $value, $expire);
    	}
    	return $res;
    }
    /**
     * 批量添加.如果某key存在则为false并且其他key也不会被保存
     * @param Array $pairs 索引数组,索引为key,值为...
     * @return Boolean
     */
	public function msetnx( $pairs){
		foreach((array)$pairs as $key=>$value){
			is_numeric($value) && ($value+= 0);
    		$res = $this->setnx($key, $value);
    	}
    	return $res;
	}
	/**
	 * 批量获取数据
	 * @param Array $keys KEY组合
	 * @return Mixed 如果成功，返回与KEY对应位置的VALUE组成的数组
	 */
	public function mget( $keys){
		$keys = (array)$keys;
		$aMongoRet = $this->find($this->mgtblactstr, array('_id' => array('$in'=> $keys), 't'=>array('$gte'=>time() ) ), array('v'));
		$aRet = $aTemp = array();
		if( ( $aMongoRet['sta'] == 1 ) && isset($aMongoRet['data']) ){
			foreach ((array)$aMongoRet['data'] as $v){
				$aTemp[$v['_id']] = $v['v'];
			}
		}
		foreach ($keys as $k => $v){
			$aRet[$k] = $aTemp[$v] ? $aTemp[$v] : '';
		}

		return $aRet;
	}
	/**
	 * 批量获取数据--对应mc
	 * @param Array $keys KEY组合
	 * @return Mixed 如果成功，返回与KEY对应位置的VALUE组成的数组
	 */
	public function mgetbymem( $keys){
		$aMongoRet = $this->find($this->mgtblactstr, array('_id' => array('$in'=> $keys), 't'=>array('$gte'=>time() ) ), array('_id','v'));
		if( ! (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ){
			return array();
		}
		$aData = array();
		foreach($aMongoRet['data'] as $row){
			$aData[$row['_id']] = $row['v'];
		}
		return $aData;
	}

	/**
	 * 判断key是否存在
	 * @param String $key
	 * @return Boolean
	 */
	public function exists( $key){
		$aMongoRet = $this->findOne($this->mgtblactstr, array('_id' => $key), array('_id') );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data']['_id'])) ? true : false;
	}
    /**
     * 获取当前db中符合匹配的key.仅支持正则中的*通配符.如->keys('*')
     * @param String $pattern
     * @return Array
     */
    public function keys( $pattern){
    }
    
    /**
     * 获取需要加最后更新时间的表名,用于清理旧数据
     */
    public function redisTables(){
    	return omongotable::redisTables();
    }
    
    /**
     * 删除某key/某些key
     * @param String/Array $keys
     * @return int 被真实删除的个数
     */
    public function delete( $keys, $force=true){
    	$keys = (array)$keys;
    	$aMongoRet = $this->remove($this->mgtblactstr, array('_id' => array('$in'=>$keys) ), false );
		if ($force === true) { //list,hash默认不删除,可加参数强制清除
			$this->remove($this->mgtblactlist, array('k' =>array('$in'=>$keys) ) );
			$this->remove($this->mgtblacthashs, array('k' =>array('$in'=>$keys) ) );
		}
    	$aMongoRet = $this->remove($this->mgtblactset, array('k' => array('$in'=>$keys) ), false );
    	$aMongoRet = $this->remove($this->mgtblactSortedSet, array('k' =>array('$in'=>$keys) ), false );
		return (($aMongoRet['sta'] == 1) && isset($aMongoRet['data'])) ? true : false;
    }
	/**
     * 删除某key
     * @return int 被真实删除的个数
     */
    public function deletebymem( $key){
    	$aMongoRet = $this->remove($this->mgtblactstr, array('_id' => $key ), false );
		return ($aMongoRet['sta'] == 1) ? true : false;
    }
    
    /**
     * kvs方法
     */
    public function put($keys, $vals=null, $op=0, $expire=0, $zip=true, $sync=true, $weight=0){
    	$keys = is_array( $keys) ? $keys : array($keys => $vals);
    	foreach($keys as $key => $val){
			if(!$val && !is_numeric($val) && !is_array($val)) continue;
			return $this->set($key, $val);
    	}
    	return false;
    }
}
