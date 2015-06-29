<?php
set_time_limit(0);
/**
 * AF_INET 基于IPv4
 * tcp:SOCK_STREAM,提供一个顺序化的、可靠的、全双工的、基于连接的字节流
 * udp:SOCK_DGRAM,无连接，不可靠、固定最大长度,因为没有状态,所以直接socket_recvfrom,socket_sendto操作就可以.
 * 
 */
$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
if(false===$socket){
	debug();
}
if(false===socket_bind($socket, '127.0.0.1', 9001)){
	debug();
}
if(false===socket_listen($socket)){
	debug();
}else{
	debug('listening...');
}

socket_set_nonblock($socket);//非阻塞
$null = NULL;

//不断地等待客户端请求
$clients = array();
while(true){

	/**
	 *  $read可以理解为一个数组，这个数组中存放的是文件描述符。当它有变化（就是有新消息到或者有客户端连接/断开）时，socket_select函数才会返回，继续往下执行
		$write是监听是否有客户端写数据，传入NULL是不关心是否有写变化
		$except是$sockets里面要被排除的元素，传入NULL是”监听”全部
		$tv_sec
		如果为0：则socket_select立即返回，有利于轮询
		如果为n>1: 则最多在n秒后结束，如遇某一个连接有新动态，则提前返回
		如果为null：无超时，socket_select() 会无限阻塞，直到有新连接过来，则返回 
		$tv_usec
	 */
	$read = array_merge($clients, array($socket));
	if(socket_select($read, $write, $null, 0, 0)){
		if(in_array($socket, $read)){
			//一旦有连接请求过来，一个新的socket资源将被创建
			$client = socket_accept($socket);
			
			if(false!==$client){
				$clients[] = $client;
				debug('accept client request');
				$test=0;
				for($i=0;$i<100000000;$i++){
					$test+=$i;
				}
				debug($test);
				$recvdata = socket_read($client, 1024, PHP_BINARY_READ);
				if(false===$recvdata){
					debug();
				}else{
					socket_write($client,'recv');
					debug($recvdata);
				}
			}
		}
	}
	$clients = array_filter($clients);
}

function debug($params=null){
	if(is_null($params)){
		echo socket_strerror(socket_last_error()).PHP_EOL;
	}else if(is_scalar($params)){
		echo $params.PHP_EOL;
	}else{
		var_dump($params);
	}
}