<?php
/**
 * 小喇叭
 * 参考http://blog.csdn.net/shagoo/article/details/6396089
 */
$host = 'local.newborn.com'; //host
$port = 9000; //port
$maxuser = 100;
chdir(dirname(__FILE__));
$script = 'test/h5/websocket'.basename(__FILE__);
$url = "ws://{$host}:{$port}/{$script}";
echo '======小喇叭======'.PHP_EOL;
$jsconfig = "var vars={};vars.server='{$url}';";
echo 'write js config ... ';
if(!file_put_contents(__DIR__.'/config.js', $jsconfig)){
	die('[fail]'.PHP_EOL);
}else{
	echo '[success]'.PHP_EOL;
}

//Create TCP/IP sream socket
echo 'creating socket ... ';
/**
 * tcp:SOCK_STREAM,提供一个顺序化的、可靠的、全双工的、基于连接的字节流
 * udp:SOCK_DGRAM,无连接，不可靠、固定最大长度
 */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if(false===$socket){
	var_dump(socket_last_error(), socket_strerror());
	exit;
}else{
	echo '[success]'.PHP_EOL;
}
/**
 * 一般来说，一个端口释放后会等待两分钟之后才能再被使用，SO_REUSEADDR是让端口释放后立即就可以被再次使用
 * 参考:http://www.cnblogs.com/mydomain/archive/2011/08/23/2150567.html
 */
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

//绑定端口
socket_bind($socket, 0, $port);

//listen to port
if(socket_listen($socket, $maxuser)){
	echo "socket listening on {$host}:{$port} ...".PHP_EOL;
}
socket_set_nonblock($socket);//非阻塞
//create & add listning socket to the list
$clients = array($socket);
$null = NULL; //null var
//start endless loop, so that our script doesn't stop
while (true) {
	/*
	$newsocket = socket_accept($socket) or die('socket accept error\n');
	
	if($res = socket_read($newsocket, 8192)){
		//var_dump(date('Y-m-d H:i:s'), $res);
		$response_text = mask(json_encode(array('type'=>'system', 'message'=>$res)));
		send_message($response_text);
	}
	*/
	//manage multipal connections
	$changed = $clients;
	socket_select($changed, $null, $null, 0, 10);
	
	//check for new socket
	if (in_array($socket, $changed)) {
		$socket_new = socket_accept($socket); //accpet new socket
		$header = socket_read($socket_new, 1024); //read data sent by the socket
		socket_getpeername($socket_new, $ip);
		socket_getsockname($socket_new, $addr);
		
		if(substr($header, 0, 6)=='notice'){//小喇叭推送消息
			$hinfo = explode('|', $header);
			$msg = $hinfo[1];
			echo "小喇叭接收到消息:{$msg}".PHP_EOL;
		}else{//其他客户端请求连接
			$clients[] = $socket_new; //add socket to client array
			perform_handshaking($header, $socket_new, $host, $port, $script); //perform websocket handshake
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' connected'))); //prepare json data
			send_message($response); //notify all users about new connection
			
			//make room for new socket
			$found_socket = array_search($socket, $changed);
			unset($changed[$found_socket]);
		}
	}
	
	//loop through all connected sockets
	foreach ($changed as $changed_socket) {
		
		//check for any incomming data
		while(socket_recv($changed_socket, $buf, 1024, 0) >= 1)
		{
			echo 'recv'.PHP_EOL;
			$received_text = unmask($buf); //unmask data
			$tst_msg = json_decode($received_text); //json decode 
			$user_name = $tst_msg->name; //sender name
			$user_message = $tst_msg->message; //message text
			$user_color = $tst_msg->color; //color
			
			//prepare data to be sent to client
			$response_text = mask(json_encode(array('type'=>'usermsg', 'name'=>$user_name, 'message'=>$user_message, 'color'=>$user_color)));
			send_message($response_text); //send data
			break 2; //exist this loop
		}
		
		$buf = @socket_read($changed_socket, 1024, PHP_NORMAL_READ);
		if ($buf === false) { // check disconnected client
			// remove client for $clients array
			$found_socket = array_search($changed_socket, $clients);
			socket_getpeername($changed_socket, $ip);
			unset($clients[$found_socket]);
			
			//notify all users about disconnected connection
			$response = mask(json_encode(array('type'=>'system', 'message'=>$ip.' disconnected')));
			send_message($response);
		}
	}
	
}
// close the listening socket
echo 'socket close'.PHP_EOL;
socket_close($sock);

function send_message($msg)
{
	global $clients;
	if(empty($clients)) return;
	foreach($clients as $changed_socket)
	{
		@socket_write($changed_socket,$msg,strlen($msg));
	}
	return true;
}


//Unmask incoming framed message
function unmask($text) {
	$length = ord($text[1]) & 127;
	if($length == 126) {
		$masks = substr($text, 4, 4);
		$data = substr($text, 8);
	}
	elseif($length == 127) {
		$masks = substr($text, 10, 4);
		$data = substr($text, 14);
	}
	else {
		$masks = substr($text, 2, 4);
		$data = substr($text, 6);
	}
	$text = "";
	for ($i = 0; $i < strlen($data); ++$i) {
		$text .= $data[$i] ^ $masks[$i%4];
	}
	return $text;
}

//Encode message for transfer to client.
function mask($text)
{
	$b1 = 0x80 | (0x1 & 0x0f);
	$length = strlen($text);
	
	if($length <= 125)
		$header = pack('CC', $b1, $length);
	elseif($length > 125 && $length < 65536)
		$header = pack('CCn', $b1, 126, $length);
	elseif($length >= 65536)
		$header = pack('CCNN', $b1, 127, $length);
	return $header.$text;
}

//handshake new client.
function perform_handshaking($receved_header,$client_conn, $host, $port, $script)
{
	$headers = array();
	$lines = preg_split("/\r\n/", $receved_header);
	foreach($lines as $line)
	{
		$line = chop($line);
		if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
		{
			$headers[$matches[1]] = $matches[2];
		}
	}

	$secKey = $headers['Sec-WebSocket-Key'];
	$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
	//hand shaking header
	$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
	"Upgrade: websocket\r\n" .
	"Connection: Upgrade\r\n" .
	"WebSocket-Origin: $host\r\n" .
	"WebSocket-Location: ws://$host:$port/{$script}\r\n".
	"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
	socket_write($client_conn,$upgrade,strlen($upgrade));
}
