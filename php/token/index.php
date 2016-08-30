<?php
session_start();

if(isset($_POST['submit'])){ //表单提交，验证token
	if(empty($_POST['token']) || empty($_SESSION['token']) || $_POST['token']!=$_SESSION['token']){
        exit('token error');
    }else{
        unset($_SESSION['token']);
		echo 'ok!';
    }
}else{ //加载表单，生成token
	$_SESSION['token'] = mt_rand(1, 9999); //每次生成一个token
	include 'index.html';
}