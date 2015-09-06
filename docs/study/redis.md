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