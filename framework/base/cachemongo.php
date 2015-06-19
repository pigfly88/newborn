<?php
!defined('ENV_PRO') && define('ENV_PRO', false);//默认为非线上环境，方便日志输出进行调试

/**
 * MongoDB
 */
class cachemongo {
    protected $cli = null; //mongodb客户端
    private $_coll = null;//文档集

    protected $logtype=2;//0不打log 1写入文件 2直接输出到屏幕
    protected $logfile;//logtype=1的时候才启用
    /**
     * 
     * 连接mongodb，实例化mongodbclient
     * @param string $server 服务器名
     * 格式：mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/db
     * @param type $opt 选项
     * 
     * @example $mongo = new cachemongo('mongodb://localhost:27017');
     * 
     */
    public function __construct($server, $opt=array('connect' => true)){
    	if(empty($server)){
    		$this->log('未找到配置');
    		return null;
    	}
        !extension_loaded('mongo') && $this->log('Mongo Extendsion Not Loaded');
        
        if(!is_object($this->cli)){
            try{
                $this->cli = new MongoClient($server, $opt);
            }catch(MongoConnectionException $e){
                $this->log($e);
            }catch(Exception $e){
                $this->log($e);
            }
        }elseif(!$this->cli->connected){
            try{
                $this->cli->connect();
            }catch(MongoConnectionException $e){
                $this->log($e);
            }catch(Exception $e){
                $this->log($e);
            }
        }

    }
    
    public function table($tbl){
    	if( (!$tbl = trim($tbl)) || ( !$atbl = explode('.', $tbl)) || (count($atbl) != 2)){
    		$this->log("[table name error]:$tbl, example:'blog.posts'");
    	}
    	return $atbl;
    }
    
    /**
     * 
     * 选择一个文档集
     * @param string $tbl 数据库名.文档集名 e.g:test.article
     * @return object MongoCollection
     */
    public function selectCollection($tbl){
        if(!is_object($this->_coll[$tbl])){
            list($db, $collection) = $this->table($tbl);
            $this->_coll[$tbl] = $this->cli->selectCollection($db, $collection);
        }
        return $this->_coll[$tbl];
    }
    
    /**
     * 
     * @param type $tbl
     * @param type $data
     * insert opt参数说明
     * [fsync]:Boolean，是否强制在返回true之前把数据同步到磁盘，默认为false，如果设置为true，将会覆盖w值重置为0
     * [j]:Boolean，是否同步写日志，如果设置为true，将会覆盖w值重置为0
     * [w]:写操作选项
     * 0-马上返回true，不关心写操作是否成功
     * 1-主服务器确认
     * N-主服务器确认，然后复制到其他服务器
     * majority-必须被所有副本确认
     * true-主服务器确认，并写日志到磁盘
     * [wtimeout]:写操作超时时间
     * [safe]:弃用，最好采用[w]选项设置
     * 
     * insert返回值:
     * 如果设置了 "w" 选项，将会返回包含插入状态的数组。 否则，将会返回一个 TRUE 代表数组不是空的（空数组将会抛出 MongoException ）。
     * 如果返回了一个 array，将会有以下键：

     * ok
     * 它应该几乎总是 1（除非 last_error 本身出现错误）。

     * err
     * 如果这个字段不是 null，说明刚才的操作出现了错误。 如果有这个字段，它将包含一个字符串，用于描述出现的错误信息。

     * code
     * 如果发生了一个数据库错误，相应的错误码会传到客户端。

     * errmsg
     * 如果数据库命令出现了错误，将会设置这个字段。同时 ok 也会是 0. 例如，设置了 w 并且超时了，errmsg 将会是 "timed out waiting for slaves" 并且 ok 是 0。 如果设置了这个字段，它会是发生的错误的字符串描述。

     * n
     * 如果最后的操作是插入、更新或删除，将会返回受影响的对象数量。对于插入操作，这个值总是 0。

     *  wtimeout
     * 等待复制直到超时的时间。

     * waited
     * 在超时前，要等待操作多久。
     * 
     * wtime   
     * 如果设置了 w 并且操作成功了，等到复制到 w 台服务器的时间。

     * upserted 
     * 如果发生了一次 upsert，这个字段将会包含新记录的 _id。 对于 upsert，不管是该字段还是 updatedExisting 都会被保留（除非发生了一个错误）。
 
     * updatedExisting
     * 如果一个 upsert 更新了一个存在的元素，这个字段将会是 true。 对于 upsert，无论是这个字段 还是 upserted 都会被保留（除非发生了错误）。
     * 
     */
    public function insert($tbl, $data, $safe=false){
    	$opt = array();
    	if($safe){
			$opt['fsync'] = false;
			$opt['j'] = 0;
			$opt['w'] = 1;
		}else{
			$opt['fsync'] = false;
			$opt['j'] = 0;
			$opt['w'] = 0;//离弦之箭
		}
    	try{
	        $coll = $this->selectCollection($tbl);
	        //$data['id'] = $this->id($tbl);//自增id
	        $ret = $coll->insert($data, $opt);//, $this->opt($safe)
    	}catch(MongoCursorTimeoutException $e){
			$this->log($e);
		}catch(MongoCursorException $e){
			$this->log($e);
		}catch(MongoException $e){
			$this->log($e);
		}catch(MongoDuplicateKeyException $e){
			$this->log($e);
		}catch(Exception $e){
			$this->log($e);
		}
		return $ret;
    }
    
    /**
     * 表和自增id的对应关系
     *
     */
    public function id($tbl){
    	list($db, $coll) = $this->table($tbl);
    	$coll = $this->selectCollection("{$db}.nfs_tid");
    	try{
    		$new = $coll->findAndModify(array('k'=>$tbl), array('$inc'=>array('v'=>1)), array('k', 'v'), array('new'=>true, 'upsert'=>true));
    	}catch(MongoResultException $e){
    		$this->log($e);
    	}catch(Exception $e){
    		$this->log($e);
    	}
    	$res = intval($new['v']);
    
    	if(!$res)	$this->log("[$tbl id incr error]");
    	return $res;
    }
    
    /**
     * 
     * @param type $tbl
     * @param type $condition
     * 
     * 文档：
     * http://docs.mongodb.org/manual/reference/method/db.collection.find/
     */

    public function find($tbl, $condition=array()){
        $coll = $this->selectCollection($tbl);
        $query = $opt = $fields = array();
        !is_array($condition) && $condition = array();
        foreach ($condition as $k=>$v){
        	if(in_array($k, array('_sort'))){
        		$opt[$k] = $v;
        	}else{
        		$query[$k] = $v;
        	}
        }
		is_array($condition['fields']) && $fields = $condition['fields'];
        $cursor = $coll->find($query, $fields);
        
        if($opt['_sort']){
        	$cursor->sort($opt['_sort']);
        }
        
        foreach ($cursor as $doc) {
        	$idk = '$id';
			$doc['_id'] = $doc['_id']->$idk;
		    $res[] = $doc;
		}
		return $res;

    }
    
    public function findOne($tbl, $query=array()){
    	$fields = array();
    	!is_array($query) && $query = array();
    	$query = $this->mongoid($query);
    	try{
	        $coll = $this->selectCollection($tbl);
	        $res = $coll->findOne($query, $fields);
	        $idk = '$id';
			$res['_id'] = $res['_id']->$idk;
    	}catch (MongoException $e){
    		$this->log($e);
    	}

		return $res;
    }
    
    public function update($tbl, $condition, $data){
        $coll = $this->selectCollection($tbl);
        return $coll->update($condition, $data);
    }
    
    public function delete($tbl, $criteria){
        $coll = $this->selectCollection($tbl);
        $criteria = $this->mongoid($criteria);
        return $coll->remove($criteria);
    }
    
    public function mongoid($criteria){
    	!empty($criteria['_id']) && $criteria['_id'] = new MongoId($criteria['_id']);
    	return $criteria;
    }
    
    /**
     * 
     * 组装选项
     * @param boolean $safe
     * @return array
     */
    private function opt($safe=false){
        return $safe ? array('w'=>0, 'j'=>1) : array('w'=>0, 'j'=>0);
    }
    
    /**
     * 
     * 统一返回
     */
    private function res(){
        
    }
    
    private function log($e){
    	if(!$this->logtype) return;
    	is_object($e) && $e = $e->getMessage();
    	$content = sprintf("[%s] [%s] %s\n", date('Y-m-d H:i:s'), 'mongo err', $e);
    	/*
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
		*/
    	switch ($this->logtype){
    		case 1:
    			file_put_contents($this->logfile, $content, FILE_APPEND);
    			
    		case 2:
    			die($content);
    	}
    }
    
    public function ret($func, $ret){
    	return $ret;
    }
    
    
    
}