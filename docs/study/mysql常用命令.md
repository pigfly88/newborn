1.登录  
> mysql [-P端口] -u{用户名} -p

-------------------------------------------------------------------------------------------------------
2.导入  
> mysql [-P端口] -u{用户名} -p  
> use {database}  
> source s.sql  

-------------------------------------------------------------------------------------------------------
3.导出  
3.1 只导出表结构  
> mysqldump --opt -d {数据库名} -u{用户名} -p d.sql  
 
3.2 导出数据不导出表结构  
> mysqldump -t {数据库名} -u{用户名} -p d.sql  
  
3.3 导出特定表的结构
> mysqldump -u{用户名} -p -B {数据库名} --table {表名} d.sql  

3.4 导出数据库
> mysqldump -u{用户名} -p {数据库名} > s.sql

-------------------------------------------------------------------------------------------------------
4.修改root密码
mysql -u root -p
mysql> SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpass');

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


********************************tips********************************  
{}花括号内的为变量，[]中括号内的为可选