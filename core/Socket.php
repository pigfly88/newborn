<?php
class Socket{

	public function send($client, $data, $timeout=1){

		$sucess = 1;
		$socket = stream_socket_client($client, $errno, $errstr, $timeout);
		if($socket){
			stream_set_timeout($socket, $timeout);
			if(false === fwrite($socket, $data)){
				$sucess = 0;
			}
			fclose($socket);
		}else{
			$sucess = 0;
		}
		if(!$sucess){
			echo 'socket send error '.socket_strerror(socket_last_error());
			return false;
		}
		return true;
	}
	
	public function get($server){
		$socket = stream_socket_server($server, $errno, $errstr);
		if(!$socket){
		  echo "socket server error: $errstr ($errno)<br />\n";
		}else{
		  while($conn = stream_socket_accept($socket)){
			fwrite($conn, 'The local time is ' . date('n/j/Y g:i a') . "\n");
			fclose($conn);
		  }
		  fclose($socket);
		}
	}
	
	public function udp(){
		$socket = stream_socket_server("udp://127.0.0.1:5555", $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN);
		var_dump($socket);exit;
		if (!$socket) {
			echo socket_strerror(socket_last_error());
			die("$errstr ($errno)");
		}
		
		do {
			$pkt = stream_socket_recvfrom($socket, 1, 0, $peer);
			echo "$peer\n";
			stream_socket_sendto($socket, date("D M j H:i:s Y\r\n"), 0, $peer);
		} while ($pkt !== false);
	}
	
	
}