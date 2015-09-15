php dll:
https://github.com/phpredis/phpredis/downloads

redis远程连接
vim redis.conf
requirepass {***} #设置密码

#重启redis
kill {redis pid}
redis-server 

#php auth验证
$connect = $redis->connect($cfg['host'], $cfg['port'], $cfg['timeout']);
$cfg['password'] && $redis->auth($cfg['password']);

------------------------------------------------------------------------------
[添加redis扩展]
1、安装phpredis
wget https://github.com/nicolasff/phpredis/archive/2.2.4.tar.gz
上传phpredis-2.2.4.tar.gz到/usr/local/src目录
cd /usr/local/src #进入软件包存放目录
tar zxvf phpredis-2.2.4.tar.gz #解压
cd phpredis-2.2.4 #进入安装目录
/usr/local/php/bin/phpize #用phpize生成configure配置文件
./configure --with-php-config=/usr/local/php/bin/php-config  #配置
make  #编译
make install  #安装
安装完成之后，出现下面的安装路径
/usr/local/php/lib/php/extensions/no-debug-non-zts-20090626/

2、配置php支持
vi /usr/local/php/etc/php.ini  #编辑配置文件，在最后一行添加以下内容
extension="redis.so"
:wq! #保存退出

3  重启服务
sudo service nginx restart
sudo /etc/init.d/php-fpm restart