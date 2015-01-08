<?php
require dirname(dirname(__DIR__)).'/helper/Socket.php';
Socket::write('127.0.0.1:1206', 'hello world', 'tcp');
