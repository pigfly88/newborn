+ [常用命令](#1)
	- [登录](#1.1)
	- [导入](#1.2)
	- [导出](#1.3)
	- [修改密码](#1.4)

+ [架构](#2)
	- [组件](#2.1)
	- [并发](#2.2)
	- [事务](#2.3)

+ [字段类型](#3)

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

+ **表锁**。
	- 开销最小，当有写操作时，mysql把整张表锁住，阻塞其他读写操作，直到当前写操作完成。写锁比读锁有更高的优先权（可以插队排到读锁前面）。适合读多写少的场景。
+ **行级锁**。
	- 可以支持最大的并发处理，同时也带来比较大的开销。**InnoDB和Falcon**存储引擎支持行级锁。适合频繁写入的场景。

<h4 id="2.3">事务</h4>

<h2 id="3">字段</h2>

	[mysql]
	bigint：2^63		8字节，范围（+-9.22*10的18次方）
	int：2^31		4字节，范围（-2147483648~2147483647）	
	mediumint：2^23	3字节，范围（-8388608~8388607）
	smallint：2^15	2字节，范围（-32768~32767）
	tinyint：2^8		1字节，范围（-128~127）

********************************tips********************************  
{}花括号内的为变量，[]中括号内的为可选



