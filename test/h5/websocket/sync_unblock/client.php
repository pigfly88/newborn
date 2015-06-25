<?php
$socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
if(false===$socket){
	debug();
}
if(false===socket_connect($socket, '127.0.0.1', 9001)){
	debug();
}
if(socket_write($socket, 'sys')){
	debug('write success');
}
socket_recv($socket, $buf, 200, 0);
debug($buf);
function debug($params=null){
	if(is_null($params)){
		echo socket_strerror(socket_last_error()).PHP_EOL;
	}else if(is_scalar($params)){
		echo $params.PHP_EOL;
	}else{
		var_dump($params);
	}
}