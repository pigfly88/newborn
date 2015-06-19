<?php
header("Content-type: text/html; charset=utf-8"); 
$host = 'local.newborn.com'; //host
$port = 9000; //port
$timeout = 1;
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('socket create error '.socket_strerror(socket_last_error()));
//stream_set_timeout($socket, $timeout);
socket_connect($socket, $host, $port) or die('socket connect error');

$data = 'push msg~';
$res = socket_write($socket, $data);
socket_recv($socket, $buf, 200, 0);
$status = $res ? '成功' : '失败';
echo "发送消息{$status}, 消息大小:{$res} bytes, 返回:{$buf}";
//socket_close($socket);