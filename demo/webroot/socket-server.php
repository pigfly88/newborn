<?php
set_time_limit(0);
/* Open a server socket to port 1234 on localhost */
$context = stream_context_create();
$server = @stream_socket_server('tcp://127.0.0.1:9987', $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN, $context);
if(FALSE === $server){
	echo "socket server create [fail], {$errstr}[{$errno}]";
	exit;
}else{
	echo 'socket server create [success]';
}
/*
do {
	$pkt = stream_socket_recvfrom($server, 1, 0, $peer);
	echo "$peer\n";
	stream_socket_sendto($server, date("D M j H:i:s Y\r\n"), 0, $peer);
} while ($pkt !== false);
*/
do {
	$client = @stream_socket_accept($server, 5);
	if(FALSE !== $client){
		$msg = fread($client, 8192);
		echo "client say: {$msg}\n";
		fwrite($client, "hello, client!");
		fclose($client);
	}
	
	
	
}while(true);


/* Close it up */

//fclose($socket);
exit;

//确保在连接客户端时不会超时


$ip = '127.0.0.1';
$port = 1935;

/*
 +-------------------------------
 *    @socket通信整个过程
 +-------------------------------
 *    @socket_create
 *    @socket_bind
 *    @socket_listen
 *    @socket_accept
 *    @socket_read
 *    @socket_write
 *    @socket_close
 +--------------------------------
 */

/*----------------    以下操作都是手册上的    -------------------*/
if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
    echo "socket_create() 失败的原因是:".socket_strerror($sock)."\n";
}

if(($ret = socket_bind($sock,$ip,$port)) < 0) {
    echo "socket_bind() 失败的原因是:".socket_strerror($ret)."\n";
}

if(($ret = socket_listen($sock,4)) < 0) {
    echo "socket_listen() 失败的原因是:".socket_strerror($ret)."\n";
}

$count = 0;

do {
    if (($msgsock = socket_accept($sock)) < 0) {
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        break;
    } else {
        
        //发到客户端
        $msg ="测试成功！\n";
        socket_write($msgsock, $msg, strlen($msg));
        
        echo "测试成功了啊\n";
        $buf = socket_read($msgsock,8192);
        
        
        $talkback = "收到的信息:$buf\n";
        echo $talkback;
        
        if(++$count >= 5){
            break;
        };
        
    
    }
    //echo $buf;
    socket_close($msgsock);

} while (true);

socket_close($sock);
?>