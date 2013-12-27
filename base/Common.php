<?php
/**
 * 加载模型
 *
 * @param string $model
 * @return object
 */
function M($model=''){
	$model = empty($model) ? substr(CONTROLLER, 0, -10) : $model;
	return Model::load($model);
}

/**
 * 读取配置文件
 *
 * @param string $path
 */
function C($path, $ext='.php'){
	static $config;
	if(isset($config[$path.$ext])) return $config[$path.$ext];
	
	$info = explode('.', $path);
	$file = CONFIG_ROOT.$info[0].$ext;
	$content = include($file);

	$res = null;
	
	if(count($info)==1){		
		$res = $content;
	}else{
		array_shift($info);
		$i=0;
		foreach($info as $v){
			if($i==0)	$res = $content[$v];
			else $res = $res[$v];
			
			$i++;
		}
		
	}
	$config[$path.$ext]=$res;
	return $res;
}

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}