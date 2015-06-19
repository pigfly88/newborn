<?php
class file{
	static $_f = array();
	
	public static function get($file){
		return file_get_contents($file);
	}
	
	/**
	 * 写文件
	 *
	 * @param string $file 文件路径
	 * @param string $data 文件内容
	 * @param string $maxsize 保存的最大尺寸 单位:mb 默认为0 不追加写入
	 * @return bollean
	 */
	public static function put($file, $data, $maxsize=0){
		$flags = $maxsize ? (is_file($file) && (filesize($file)/(1024*1024) < $maxsize) ? FILE_APPEND : 0) : 0;
		return file_put_contents($file, $data, $flags);
	}
	
	public static function upload($param, $folder, $func=''){
		if(!is_array($_FILES[$param]) || empty($_FILES[$param])){
			$res['fail'][] = -1;
			return $res;
		}
		
		
		$file = $_FILES[$param];
		
		if(is_array($file['tmp_name'])){
			$files = $file;
		}else{
			foreach ($file as $k=>$v){
				$files[$k] = array($v);
			}
		}

		self::mkdir($folder);
		foreach ($files['tmp_name'] as $k=>$v){
			self::parse_error($files[$k]);
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
	
	protected static function parse_upload_error($file){
		switch ($file['error']){
			case UPLOAD_ERR_OK://其值为 0,没有错误发生,文件上传成功
				break;
			case UPLOAD_ERR_INI_SIZE://其值为 1,上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值
				echo 1;
		}
	}
	
	public static function mkdir($dir, $mode=0777){
		if(is_dir($dir)) return true;
		else{
			$dirs = explode('/', $dir);
			foreach ($dirs as $v) {
				$curdir .= $v.'/';
				if(!is_dir($curdir)){
					mkdir($curdir);
					chmod($curdir, $mode);
				}
			}
		}
	}
	
	public static function listdir($dir){
		$files = array();
		if(is_dir($dir)) {
			if($files = scandir($dir)) {
				$files = array_slice($files,2);
			}
		}
		return $files;
	}
	
	public static function import($file){
		if(empty($file)){
			return self::$_f;
		}else if(isset(self::$_f[$file])){
		
		}else if(!is_file($file)){
			return false;
		}else{
			self::$_f[$file] = include $file;
		}
		return self::$_f[$file];
	}
	
	
	
	
	
	
	
}