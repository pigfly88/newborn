<?php
/**
 * NFS框架初始化文件
 *
 * @version        2013 Fri Dec 27 09:50:23 GMT 2013
 * @author         justlikeheaven <328877098@qq.com>
 * @link           https://github.com/justlikeheaven/NFS.git
 */

define('NFS_VERSION', '1.0');

define('FRAMEWORK', 'NFS');

//开始时间
define('TIME', time());

//文件分隔符
define('DS', '/');

//PHP文件后缀
define('PHP_EXT', '.php');

//Controller文件修饰符
define('CONTROLLER_EXT', '_c');

//Model文件修饰符
define('MODEL_EXT', '_m');

//默认控制器
define('DEFAULT_CONTROLLER', 'index');

//默认方法
define('DEFAULT_ACTION', 'index');

define('BEFORE', 'before');

define('AFTER', 'after');

define('SEPARATOR', '_');

define('COMMON_FLAG', '~~');

define('TOKEN', base64_encode("I'm NFS"));

//NFS框架根目录
define('NFS_ROOT', __DIR__.DS);

//NFS base目录
define('NFS_BASE_ROOT', NFS_ROOT.'base'.DS);

//NFS helper目录
define('NFS_HELPER_ROOT', NFS_ROOT.'helper'.DS);

//项目控制器文件夹名称
define('CONTROLLER_FOLDER_NAME', 'c');

//项目模型文件夹名称
define('MODEL_FOLDER_NAME', 'm');

//项目模板文件夹名称
define('VIEW_FOLDER_NAME', 'v');

//项目配置文件夹名称
define('CONFIG_FOLDER_NAME', 'cfg');

!defined('APP_DIR') && define('APP_DIR', '');
define('CONTROLLER_ROOT', APP_ROOT.DS.APP_DIR.DS.CONTROLLER_FOLDER_NAME.DS);
define('MODEL_ROOT', APP_ROOT.DS.MODEL_FOLDER_NAME.DS);
define('CONFIG_ROOT', APP_ROOT.DS.APP_DIR.DS.CONFIG_FOLDER_NAME.DS);
define('VIEW_ROOT', APP_ROOT.DS.APP_DIR.DS.VIEW_FOLDER_NAME.DS);

require NFS_BASE_ROOT.'component.php';
require NFS_BASE_ROOT.'oo.php';
oo::include_file(NFS_BASE_ROOT.'func.php');
oo::include_file(NFS_BASE_ROOT.'controller.php');
oo::base('file')->import(NFS_BASE_ROOT.'log.php');
oo::base('file')->import(NFS_BASE_ROOT.'db.php');
class NFS{
	public static $controller;
	public static $action;	
	public static $cfg;
	
	public static function run(){
		
		self::$controller = $controller = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']) : DEFAULT_CONTROLLER;
		$ctl = oo::c();
		$resful = '_'.strtolower($_SERVER['REQUEST_METHOD']);
		if( ($a=strtolower($_REQUEST['a'])) && method_exists($ctl, $a) )	$act = $a;
		elseif(method_exists($ctl, $resful))	$act = $resful;
		elseif(method_exists($ctl, DEFAULT_ACTION))	$act = DEFAULT_ACTION;
		else die('error action');
		self::$action = $act;

		$ctl->$act();
		
		/**
		 * 通用方法调度
		 * 应付普通的增删改查功能
		 * 表名和字段经过加密之后放到表单，这边会解析出来，加密的token在配置文件中设置
		 */
		/*
		if(substr($act, 0, 2) == str_repeat(SEPARATOR, 2)){
			//调度前执行before方法
			$act_before = $act.SEPARATOR.BEFORE;
			method_exists($ctl, $act_before) && $ctl->$act_before();
			
			list($func, $table) = explode(SEPARATOR, substr($act, 2));
			if(in_array($func, array('insert', 'update', 'delete', 'select'))){
				//根据表字段过滤请求参数
				$m = Model::load($table);
				//var_dump($m->columns);exit;
				if(is_array($m->columns) && !empty($m->columns)){
					foreach ($m->columns as $v){
						if(isset($_REQUEST[$v['COLUMN_NAME']])){
							$data[$v['COLUMN_NAME']] = $_REQUEST[$v['COLUMN_NAME']];
						}
					}
				}
				var_dump($data);
				!empty($data) && $m->$func($data);
			}
			
			//调度后执行after方法
			$act_after = $act.SEPARATOR.AFTER;
			method_exists($controller, $act_after) && $controller->$act_after($res);
		}
		*/
	}
	
    
   

	
	
	
}