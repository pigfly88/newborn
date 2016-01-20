<?php
/**
 * 业务cmsapi和后台共用的api类
 * @author OuyangLiu
 */
class cmsapi{
	protected static $encodeStr = '53cfdd74b412a4c6f065f10ca13159e1';
	protected static $mustParams = array('class', 'sig');
	protected static $errorInfo = array();  //错误信息
	
	/**
	 * 设置加密码字串
	 * @param string $encodeStr
	 */
	public static function setEncodeStr( $encodeStr){
		self::$encodeStr = $encodeStr;
	}
	
	/**
	 * 获取cmsapi地址
	 */
	private static function getSendUrl(){
		if(stristr($_SERVER['SERVER_NAME'], 'vm.boyaa.com') !== false){
			return "http://{$_SERVER['SERVER_NAME']}/majiang_php/zh_cn/demo/majiang_v5/cmsapi.php";
		}
		return "http://{$_SERVER['SERVER_ADDR']}/majiang_v5/cmsapi.php";
	}
	
	/**
	 * 发送数据
	 * @param array $aData 
	 */
	public static function sendData( $aData ){
		if( empty($aData) || !is_array($aData) ){
			self::setError(-111, "aData is empty");
			return false;
		}
		if(!defined('GAMEID')){
			self::setError(-888, "GAMEID is undefined");
			return false;
		}
		$_REQUEST['g'] = GAMEID;
		$aData = array_merge($_REQUEST, $aData);
		$aData['sig'] = self::getSig($aData);
		if( !self::checkMustParams($aData) ){ 
			return false;
		}
		return self::useCurl( $aData );
	}

	
	/**
	 * 验证数据
	 * @param array $aData
	 */
	public static function checkData( $aData ){
		if( !self::checkMustParams($aData) ){ 
			return false;
		}
		$sig = $aData['sig'];
		unset($aData['sig']);
		$sig_tmp = self::getSig($aData);
		if( strcasecmp($sig, $sig_tmp) != 0 ){
			self::setError(-222, "sig error");
			return false;
		}
		return true;
	}
	
	/**
	 * 必需字段 验证
	 * @param array $aData
	 */
	protected static function checkMustParams( $aData ){
		foreach ( (array)self::$mustParams as $key ){
			if( !isset($aData[$key]) ){
				self::setError(-333, "param $key not exist");
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 生成sig
	 * @param array $aData
	 */
	protected static function getSig( $aData ){
		ksort($aData);
		$sData = str_replace(array("\'", "\"", "\\"), array('', '', ''), http_build_query($aData));
		return md5($sData.self::$encodeStr);
	}
	
	/**
	 * 使用curl发送数据
	 * @param array $aData
	 */
	protected static function useCurl( $aData ){
		if( !is_array($aData) ){
			self::setError(-444, "function useCurl params error");
			return '';
		}
		$dataStr = http_build_query($aData);
		$result = ''; //返回的内容
		$useragent = 'majiang (curl)' . phpversion(); //Agent头信息
		if(function_exists('curl_init')){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, self::getSendUrl());
			curl_setopt($ch, CURLOPT_POSTFIELDS, $dataStr);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 15);
			$result = curl_exec($ch);
			if( curl_errno($ch) ){
				self::setError(-444, " curl failure");
				fc::debug(array(date('Ymd H:i:s'), self::getSendUrl(), $result), 'curl_err.txt');
				$result = '';
			}
			curl_close($ch);
		}else{
			$result = @file_get_contents( self::$sendUrl . "?" .$dataStr );
		}
		return $result;
	}
    
	/**
	 * 记录一个错误
	 * @param int $type
	 * @param string $info
	 */
	protected static function setError( $type, $info ){
		self::$errorInfo = array( 'type'=>$type, 'desc'=>$info );
		return true;
	}
	
	/**
	 * 获取最后一个错误信息
	 */
	public static function getLastError(){
		return json_encode(self::$errorInfo);
	}
}
