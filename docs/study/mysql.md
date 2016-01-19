+ [常用命令](#1)
	+ [登录](#1.1)
	+ [导入](#1.2)
	+ [导出](#1.3)
	+ [修改密码](#1.4)
+ [架构](#2)
	+ [组件](#2.1)
	+ [并发](#2.2)
	+ [事务](#2.3)
+ [字段类型](#3)
+ [基准测试与性能分析](#4)
	+ [基准测试](#4.1)
	+ [并发](#2.2)
	+ [事务](#2.3)
	
<h2 id="1">常用命令</h2>
<h4 id="1.1">登录</h4>
> mysql [-h地址] [-P端口] -u{用户名} -p

<h4 id="1.2">导入</h4>
> mysql [-P端口] -u{用户名} -p  
> use {database}  
> source s.sql  

<h4 id="1.3">导出</h4>
+ 只导出表结构  
> mysqldump --opt -d {数据库名} -u{用户名} -p d.sql  
 
+ 导出数据不导出表结构  
> mysqldump -t {数据库名} -u{用户名} -p d.sql  
  
+ 导出特定表的结构
> mysqldump -u{用户名} -p -B {数据库名} --table {表名} d.sql  

+ 导出数据库
> mysqldump -u{用户名} -p {数据库名} > s.sql

<h4 id="1.4">修改root密码</h4>
> mysql -u root -p
> mysql> SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpass');

<h4 id="1.5">远程连接</h4>
> grant all PRIVILEGES on {dbname}.* to 'username'@'%' identified by '{password}';
> 
> flush privileges;

<h4 id="1.6">建库</h4>
create database buxun_dev character set 'utf8' collate 'utf8_general_ci';

<h4 id="1.7">编码</h4>
+ 服务端
> alter database {db} character set utf8;

+ 客户端
> set names gbk;


<h4 id="1.8">建表</h4>

	[mysql]
	CREATE TABLE `seller` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `name` varchar(100) NOT NULL COMMENT '名称',
	  `desc` text NOT NULL COMMENT '描述',
	  `img` varchar(100) NOT NULL DEFAULT '' COMMENT '商家图片',
	  `shop_address` varchar(100) NOT NULL DEFAULT '' COMMENT '门市地址',
	  `depot_address` varchar(100) NOT NULL DEFAULT '' COMMENT '仓库地址',
	  `tel` varchar(20) NOT NULL DEFAULT '' COMMENT '门市电话',
	  `mobile` varchar(11) NOT NULL DEFAULT '' COMMENT '手机号码',
	  `fax` varchar(50) NOT NULL DEFAULT '' COMMENT '传真',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='商家';


<h2 id="2">mysql架构</h2>
<h4 id="2.1">组件</h4>
![](http://pic002.cnblogs.com/images/2012/152332/2012031510324452.png)

每个客户端的查询都再一个单独线程中完成，服务器负责缓存线程。解析select查询之前，mysql会先查询缓存，找到缓存则直接返回，否则会经过优化器对sql进行优化，包括重写查询、改变读表顺序、选择索引等。

<h4 id="2.2">并发</h4>
mysql通过**读锁和写锁**解决并发带来的问题。读锁是共享的：同一时间，多个用户可以同时读取同一个资源，互不干扰。写锁是排他的：一个写锁会阻塞其他读锁和写锁，同一时间，只有一个用户能写入资源，其他用户不能读和写。因此锁的粒度对于并发影响很大，锁的粒度越小，就只会锁定部分数据，而不影响读写其他数据，能承受的并发就越大。

+ **表级锁**。
	- 开销最小，当有写操作时，mysql把整张表锁住，阻塞其他读写操作，直到当前写操作完成。写锁比读锁有更高的优先权（可以插队排到读锁前面）。适合读多写少的场景。
+ **行级锁**。
	- 可以支持最大的并发处理，同时也带来比较大的开销。**InnoDB和Falcon**存储引擎支持行级锁。适合频繁写入的场景。

InnoDB和Falcon存储引擎采用行级锁+**MVCC(多版本并发控制)**应对并发问题。

MVCC原理：每个数据行都有两个属性：1.创建版本id(**cver**);2删除版本id(**dver**)。当一个事务执行查询的时候会拿当前版本id(**ver**)和这两个id对比(每开始一个事务，ver累加)，以下列出增删查改时的详细过程：

+ SELECT
	+ ver>cver
	+ !dver || dver>ver
+ INSERT
	+ 记录cver
+ DELETE
	+ 记录dver
+ UPDATE
	+ 拷贝行，写入cver，更新旧行的dver

这么做使大多数读操作**不用加锁**，使读操作尽可能的快，因为只要选取符合标准的行即可。

<h4 id="2.3">事务</h4>
<p>A向B转账100块，要执行如下操作：</p>
1. 账户A存款-100
2. 账户B存款+100

假如1执行了2没执行，那么A的存款就白白少了100块，B的存款也没加。
事务就是要保证数据流动安全，它能保证1和2是一组原子性的操作，要么全部执行，要么一个都不执行。

+ 事务必须满足ACID原则
	- 原子性：要么全部执行，要么一个都不执行
	- 一致性：数据前后变化符合预期，不会多了也不会少了
	- 永久性：保证数据安全记录下来，即使当机
	- 隔离性：一个事务的结果只有在完成以后才对其它事务可见
		- read committed 读取提交
		- read uncommitted 读取未提交
		- repeatable read 可重读
		- serializable 串行化


	[mysql]
	UPDATE `user` SET `age`=18 WHERE `id`=1;
	UPDATE `user` SET `age`=18 WHERE `id`=2;
	
	UPDATE `user` SET `age`=26 WHERE `id`=2;
	UPDATE `user` SET `age`=26 WHERE `id`=1;

上面两个事务，当他们执行完第一条语句，执行第二条语句的时候发现被对方锁定了，便产生**死锁**。

死锁的处理方式可以是超时退出，但会导致很慢的查询。

InnoDB在这种情况下可以预知并立即返回错误，处理方式是**回滚拥有最少排它行级锁的事务。**

<h2 id="3">字段类型</h2>
	[mysql]
	bigint：2^63		8字节，范围（+-9.22*10的18次方）
	int：2^31		4字节，范围（-2147483648~2147483647）	
	mediumint：2^23	3字节，范围（-8388608~8388607）
	smallint：2^15	2字节，范围（-32768~32767）
	tinyint：2^8		1字节，范围（-128~127）

###tips
{}内的为变量，[]内的为可选

<h2 id="4">基准测试与性能分析</h2>
<h4 id="4.1">基准测试</h4>
+ 指标
	+ 吞吐量。时间单位的事务处理量
	+ 响应时间。利用周期性图标准确反映数值
	+ 扩展性。当数据库大小发生改变、连接数或者硬件发生改变的时候

+工具
	ab -n1000 -c10 http://123.57.92.9/?c=product&id=29
	请求总数1000 并发请求10
	D:\xampp\apache\bin>ab -n1000 -c10 http://123.57.92.9/?c=product&id=29
This is ApacheBench, Version 2.3 <$Revision: 655654 $>
Copyright 1996 Adam Twiss, Zeus Technology Ltd, http://www.zeustech.net/
Licensed to The Apache Software Foundation, http://www.apache.org/

Benchmarking 123.57.92.9 (be patient)
Completed 100 requests
Completed 200 requests
Completed 300 requests
Completed 400 requests
Completed 500 requests
Completed 600 requests
Completed 700 requests
Completed 800 requests
Completed 900 requests
Completed 1000 requests
Finished 1000 requests


Server Software:        nginx/1.4.4
Server Hostname:        123.57.92.9
Server Port:            80

Document Path:          /?c=product
Document Length:        0 bytes

Concurrency Level:      10
Time taken for tests:   42.859 seconds 总耗时
Complete requests:      1000
Failed requests:        0
Write errors:           0
Non-2xx responses:      1000
Total transferred:      199000 bytes
HTML transferred:       0 bytes
Requests per second:    23.33 [#/sec] (mean) 吞吐量，每秒处理的请求数
Time per request:       428.594 [ms] (mean)
Time per request:       42.859 [ms] (mean, across all concurrent requests)
Transfer rate:          4.53 [Kbytes/sec] received

Connection Times (ms)
              min  mean[+/-sd] median   max
Connect:       31   43   9.6     47      94
Processing:    47  383  37.5    375     547
Waiting:       31  227 104.8    219     531
Total:         78  426  39.4    422     594

Percentage of the requests served within a certain time (ms) 时间分布
  50%    422 50%的请求处理时间不超过422ms
  66%    422
  75%    438
  80%    438
  90%    469
  95%    500
  98%    531
  99%    547
 100%    594 (longest request)