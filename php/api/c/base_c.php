<?php
class base_c extends controller {
	protected $result = array('result'=>'', 'desc'=>'', 'success'=>0); 
	public static $err = array(
		'ERROR' => array('result'=>0, 'desc'=>'发生错误'),
		'ILLEGAL' => array('result'=>-1, 'desc'=>'非法请求'),
	);
	
	function __init(){
		$token = $this->req('token');
		$_success = 1;
		
		if(ENV_PRO && !oo::base('token')->token_verify($token) && 'zhupp1988'!=$this->req('debug')){
			$this->json(self::$err['ILLEGAL']);
		}
		
	}
	
	function response($success=true, $result='', $desc=''){
		$res = array();
		$success && $res['success'] = 1;
		$result && $res['result'] = $result;
		$desc && $res['desc'] = $desc;
		
		return parent::json(array_merge($this->result, $res));
	}
	
	function success($res=''){
		return parent::json(array_merge($this->result, array('result'=>$res, 'success'=>1)));
	}
	
	function fail($res='', $desc=''){
		return parent::json(array_merge($this->result, array('result'=>$res, 'desc'=>$desc)));
	}
}