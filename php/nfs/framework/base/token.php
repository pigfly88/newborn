<?php
class token {
	
	/**
	 * 校验口令
	 *
	 * @param string $token 口令
	 * @param intval $expire 有效时间 单位秒
	 * @return unknown
	 */
	static function token_verify($token, $expire=10){
		$pass = false;//是否验证通过，默认不通过
		$secret_key = 'qO~H#!Z$j)@*p&#';//密钥
		$text = substr($token, 0, 6);//明文，加密用
		$secret_len = 12;//密文长度
		$secret_key_len = intval(substr($token, 6, 1));//用于加密的密钥长度，使用动态长度的密钥来进行加密，增加破解难度
	
		$secret_key = substr($secret_key, 0, $secret_key_len);
		$time = hexdec(substr($token, 19, strlen($token)));
		$timediff = $_SERVER['REQUEST_TIME'] - ord($text)*$secret_key_len - $time;
	
		if($timediff<=10 && $timediff>=0){
			if($secret_key_len>1 && $secret_key_len<=9){
				$secret = substr($token, 7, $secret_len);//通过密钥和明文加密过的密文
				if(substr(md5(md5($text).$secret_key), 0, $secret_len)==$secret){
					$pass=true;//验证通过
				}
			}
		}
	
		return $pass;
	}

	/**
	 * 生成口令
	 * 6位随机数字字母组合+1位密钥长度(2~9)+12位密文+8位十六进制字符串(时间)，总共27位
	 */
	
	static function token_create($expire=10){
		$secret_key = 'qO~H#!Z$j)@*p&#';//密钥
		$secret_key_len = mt_rand(2, 9);//用于加密的密钥长度，使用动态长度的密钥来进行加密，增加破解难度
		$secret_len = 12;//密文长度
	
		$text = str_rand(6, false);//6位随机数字和字母组合
		$secret_key = substr($secret_key, 0, $secret_key_len);
		$time = dechex($_SERVER['REQUEST_TIME']-ord($text)*$secret_key_len);
	
		return $text.$secret_key_len.substr(md5(md5($text).$secret_key), 0, $secret_len).$time;
	
	}
	
	static function str_rand($len=6, $with_arabic=true){
		if($len<=0) return '';
		$str = '1234567890';
		$with_arabic && $str.='aAbBcCdDeEfFgGhHiIjJkKlLmMnNoOpPqQrRsStTuUvVwWxXyYzZ';
		for($i=0; $i<$len; $i++){
			$res.=$str[mt_rand(0, strlen($str)-1)];
		}
		
		return $res;
	}
}