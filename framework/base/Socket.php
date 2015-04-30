<?php
class socket{

	protected static $proto = array('udp'=>SOCK_DGRAM, 'tcp'=>SOCK_STREAM);
	
	public static function write($addr, $data, $proto='udp', $timeout=1){
		$socket = socket_create(AF_INET, self::$proto[$proto], getprotobyname($proto)) or die('socket create error '.socket_strerror(socket_last_error()));
		list($host, $port) = explode(':', $addr);
		stream_set_timeout($socket, $timeout);
		socket_connect($socket, $host, $port) or die('socket connect error');
		
		$res = socket_write($socket, $data);
		socket_close($socket);

		return $res;
	}
	
	public static function read($addr, $proto='udp'){
		$socket = socket_create(AF_INET, self::$proto[$proto], getprotobyname($proto)) or die('socket create error '.socket_strerror(socket_last_error()));
		//socket_set_blocking($socket, 0);//0-非阻塞   1-阻塞
		list($host, $port) = explode(':', $addr);
		socket_bind($socket, $host, $port) or die('socket bind error\n');
		socket_listen($socket,4) or die('socket listen error\n');

		while(1){
			$newsocket = socket_accept($socket) or die('socket accept error\n');	
			//socket_write($newsocket, date('Y-m-d H:i:s'));
			if($res = socket_read($newsocket, 8192)){
				//sleep(5);
				var_dump(date('Y-m-d H:i:s'), $res);
			}
			
		}
	}

	
}