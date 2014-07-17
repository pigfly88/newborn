<?php
/**
 * MongoDB driver
 */
//defined('BASEPATH') or die('acess deny');

require_once dirname(__FILE__).'/Cache.php';

class CacheMongo extends Cache{
    protected $cli = null; //mongodb客户端
    private $_coll = null;//文档集
    /**
     * 
     * 连接mongodb，实例化mongodbclient
     * @param string $server 服务器名
     * 格式：mongodb://[username:password@]host1[:port1][,host2[:port2:],...]/db
     * @param type $opt 选项
     * 
     */
    public function __construct($server='', $opt=array('connect' => true)){
        !extension_loaded('mongo') && exit('Mongo Extendsion Not Loaded');
        if(!is_object($this->cli)){
            try{
                $this->cli = new MongoClient($server, $opt);
            }catch(MongoConnectionException $e){
                var_export($e, true);
            }catch(Exception $e){
                var_export($e, true);
            }
        }elseif(!$this->cli->connected){
            try{
                $this->cli->connect();
            }catch(MongoConnectionException $e){
                var_export($e, true);
            }catch(Exception $e){
                var_export($e, true);
            }
        }
    }
    
    /**
     * 
     * 选择一个文档集
     * @param string $tbl 数据库名.文档集名 e.g:test.article
     * @return object MongoCollection
     */
    public function selectCollection($tbl){
        if(!is_object($this->_coll[$tbl])){
            echo 'new coll:'.$tbl."\n";
            list($db, $collection) = explode('.', $tbl);
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
        $coll = $this->selectCollection($tbl);
        $res = $coll->insert($data, $this->opt($safe));
        return $res;
    }
    
    /**
     * 
     * @param type $tbl
     * @param type $condition
     * array('q'=>array(), 'f'=>array(), 'o'=>array(), 's'=>2, 'l'=>2)
     * q:query 查询条件
     * f:fields 筛选字段
     * o:order/sort 排序
     * s:skip 忽略前面n条记录
     * l:limit 限定文档数
     * snapshot（未实现）:
     * 快照 由于mongodb的结果集是不断变化的，如果find之后有其他新数据满足条件，也会出现在当前结果集里面。
     * 所以如果要固定结果集，find之后要snapshot一下，但：snapshot() cannot be used with sort() or hint()。
     * @return array $res
     * 
     * e.g:
     * 
     * //condition
     * $cdt = array(
            'q'=>array(
                'name'=>'zhupp', 
                //'age'=>array('$lte'=>30, '$gte'=>18)
            ), 
            'f'=>array('name', 'age'), 
            'o'=>array('age'=>-1),
            'l'=>2,
            //'s'=>2,
        );

        $res = $mongo->find('test.article', $cdt);
     * 
     * 
     * 
     * 
     * 
     * 
     * 文档：
     * http://docs.mongodb.org/manual/reference/method/db.collection.find/
     */
    public function find($tbl, $condition){
        $coll = $this->selectCollection($tbl);
        $cursor = $coll->find($condition['q'], $condition['f']);
        //$condition['snapshot'] && ($cursor = $cursor->snapshot());//快照，固定结果集(未实现)
        $condition['o'] && ($cursor = $cursor->sort($condition['o']));      
        $condition['s'] && ($cursor = $cursor->skip($condition['s']));
        $condition['l'] && ($cursor = $cursor->limit($condition['l']));
        
        $res = array();
        while($tmp = $cursor->getNext()){
            $tmp['_id'] = $cursor->key();
            $res[] = $tmp;
        }
        return $res;
    }
    
    public function update(){
        
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
    
}