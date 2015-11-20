* [1.常用命令](#1) 
* [1.1登录](#1.1) 
* [1.2导入](#1.2) 
* [1.3导出](#1.3) 
* [1.4修改密码](#1.4)

<h2 id="1">1.常用命令</h2>
<h4 id="1.1">1.1登录</h4>
> mysql [-P端口] -u{用户名} -p

<h4 id="1.2">1.2导入</h4>
> mysql [-P端口] -u{用户名} -p  
> use {database}  
> source s.sql  

<h4 id="1.3">1.3导出</h4>
只导出表结构  
> mysqldump --opt -d {数据库名} -u{用户名} -p d.sql  
 
导出数据不导出表结构  
> mysqldump -t {数据库名} -u{用户名} -p d.sql  
  
导出特定表的结构
> mysqldump -u{用户名} -p -B {数据库名} --table {表名} d.sql  

导出数据库
> mysqldump -u{用户名} -p {数据库名} > s.sql

<h4 id="1.4">1.4修改root密码</h4>
> mysql -u root -p
> mysql> SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpass');

-------------------------------------------------------------------------------------------------------
5.远程连接
> grant all PRIVILEGES on {dbname}.* to 'username'@'%' identified by '{password}';
> flush privileges;

-------------------------------------------------------------------------------------------------------
6.建库
create database buxun_dev character set 'utf8' collate 'utf8_general_ci';

7.修改数据库编码
alter database {db} character set utf8;

8.建表
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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='商家';


9.mysql命令行工具查询结果中文乱码
命令行不支持utf8，执行命令: set names gbk;

-----------------------------------------字段类型-----------------------------------------
bigint：2^63
int：2^31
mediumint：2^23
smallint：2^15
tinyint：2^8
Type	Storage	Minimum Value	Maximum Value
 	(Bytes)	(Signed/Unsigned)	Signed/Unsigned)
TINYINT	1	-128	127
 	 	0	255
SMALLINT	2	-32768	32767
 	 	0	65535
MEDIUMINT	3	-8388608	8388607
 	 	0	16777215
INT	4	-2147483648	2147483647
 	 	0	4294967295
BIGINT	8	-9223372036854775808	9223372036854775807
 	 	0	18446744073709551615
如果是unsigned则只有正数，大小增大一倍。

-----------------------------------------explain-----------------------------------------


********************************tips********************************  
{}花括号内的为变量，[]中括号内的为可选



