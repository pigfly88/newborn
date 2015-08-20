1.登录  
> mysql [-P端口] -u{用户名} -p

2.导入  
> mysql [-P端口] -u{用户名} -p  
> use {database}  
> source s.sql  

3.导出  
3.1 只导出表结构  
> mysqldump --opt -d {数据库名} -u{用户名} -p d.sql  
 
3.2 导出数据不导出表结构  
> mysqldump -t {数据库名} -u{用户名} -p d.sql  
  
3.3 导出特定表的结构
> mysqldump -u{用户名} -p -B {数据库名} --table {表名} d.sql  

**tips**  
{}花括号内的为变量，[]中括号内的为可选