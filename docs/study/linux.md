+ [常用命令](#1)
+ [文件系统](#2)


<h2 id="1">常用命令</h2>
查看进程列表
netstat -tunpl

查看系统资源统计
top

服务器速度测试
1.ping 123.57.92.9 -t
每一个被发送出的IP信息包都有一个TTL域，该域被设置为一个较高的数值（在本例中ping信息包的TTL值为255)。当信息包在网络中被传输时，TTL的域值通过一个路由器时递减1；当TTL 递减到0时，信息包被路由器抛弃。
TTL通常表示包在被丢弃前最多能经过的路由器个数。

2.tracert 123.57.92.9
这个是看看测试点到达目标服务器需要经过多少个路由器，并且可以根据经过的每个路由的毫秒数字看出慢在那个路由器，并通过ip nslookup 
来查看这个ip属于那个运营商的，甚至那个省市的运营商的，这样就一目了然了。

正确的关机方法
async #数据写入磁盘
shutdown -h 10 "shutdown after 10 minutes" #通知用户再过10分钟就关机
shutdown -r 10 "shutdown after 10 minutes" #通知用户再过10分钟就重启

<h2 id="2">文件系统</h2>
user用户 group用户组 others其他人
用户信息保存在/etc/passwd

[root@iZ25het8xn8Z ~]# ls -al
total 56
dr-xr-x---.  3 root root 4096 Jan 20 21:36 .
dr-xr-xr-x. 23 root root 4096 Aug 27 22:17 ..
-rw-------   1 root root 6859 Jan 19 22:24 .bash_history

dr-xr-xr-x. 23 root root 4096 Aug 27 22:17 ..
[文件权限] [连接数] [用户] [用户组] [文件大小(单位B)] [修改时间] [文件名]
文件权限含义：
d | r-x | r-x | r-x
第一部分：文件类型。d代表dir目录，-代表文件，l代表是link连接文件，b代表存储设备，c代表串行设备，例如鼠标和键盘
第二部分：所有者权限
第三部分：用户组权限
第四部分：其他人的权限

rwx对于文件的含义
r-read可读
w-write可写（修改、新增，但不包括删除）
x-可执行

rwx对于目录的含义
r-读取目录结构权限，但并不是可以进入到该目录，切换到该目录需要x权限
w-新建、删除、修改文件或目录
x-代表用户是否有权限进入该目录
chgrp -R www test #修改文件所属用户组，-R表示递归 将test目录所属组改成www
chown -R www test #修改文件所属用户
chmod -R 777 test #修改文件权限，r-4,w-2,x-1