<?php
if(isset($_POST['submit'])){
	$token = $_POST['token'];
	$rand = $_POST['rand'];
	$token = base64_decode($token);
	$time = substr($token, 0, 10);
	$correct_token = md5($rand.'abc');
	$post_token = substr($token, -32);
	
	if(time()-$time>1800 || $correct_token!=$post_token){ //有效期半小时
		exit('error');
	}else{
		echo 'ok!';
	}
}else{
	include 'api.html';
}