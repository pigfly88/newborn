<?php
class upload {
	public static function exec($param, $folder, $func=''){
		if(!is_array($_FILES[$param]) || empty($_FILES[$param])){
			$res['fail'][] = -1;
			return $res;
		}
	
		self::mkdir($folder);
		$file = $_FILES[$param];
		if(is_array($file['tmp_name'])){
			$files = $file;
		}else{
			foreach ($file as $k=>$v){
				$files[$k] = array($v);
			}
		}
	
		foreach ($files['tmp_name'] as $k=>$v){
			if(is_uploaded_file($v)){
				$name_e = explode('.', $files['name'][$k]);
				$type = array_pop($name_e);
	
				$filename = $files["name"][$k];
				if(empty($func)){
					$filename = date('Ymd').substr(time(), -4).mt_rand(1, 99999).substr(md5($filename), -6).'.'.$type;
				}
	
				if(move_uploaded_file($v, $folder.'/'.$filename)){
					$res['success'][] = $filename;
				}else{
					$res['fail'][] = -2;
					break;
				}
			}else{
				$res['fail'][] =-3;
			}
		}
		if($res['success'] && !$res['fail']){
			$res['ok'] = count($res['success']);
		}
		return $res;
	}
}