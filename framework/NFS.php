<?php
/**
 * NFS框架初始化文件
 *
 * @version        2013 Fri Dec 27 09:50:23 GMT 2013
 * @author         justlikeheaven <328877098@qq.com>
 * @link           https://github.com/justlikeheaven/NFS.git
 */

define('VERSION', '1.0');

define('FRAMEWORK', 'NFS');

//开始时间
define('TIME', time());

//文件分隔符
define('DS', DIRECTORY_SEPARATOR);

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

//项目控制器文件夹名称
define('CONTROLLER_FOLDER_NAME', 'c');

//项目模型文件夹名称
define('MODEL_FOLDER_NAME', 'm');

//项目模板文件夹名称
define('VIEW_FOLDER_NAME', 'v');

//项目配置文件夹名称
define('CONFIG_FOLDER_NAME', 'cfg');

define('CONTROLLER_ROOT', APP_ROOT.DS.CONTROLLER_FOLDER_NAME.DS);
define('MODEL_ROOT', APP_ROOT.DS.MODEL_FOLDER_NAME.DS);
define('CONFIG_ROOT', APP_ROOT.DS.CONFIG_FOLDER_NAME.DS);
define('VIEW_ROOT', APP_ROOT.DS.VIEW_FOLDER_NAME.DS);

require_once(NFS_BASE_ROOT.'Component.php');
require_once(NFS_BASE_ROOT.'oo.php');
class NFS{
	protected static $_loaded;
	
	public static $controller;
	public static $action;	
	public static $approot;
	
    protected static $_obj;
    
    
	
	public static function autoload($class){
		
		$res = false;
		
		if(false!==strpos($class, CONTROLLER_EXT))
			$res = self::load(APP_ROOT.DS.CONTROLLER_NAME.DS.$class.PHP_EXT);
		else if(false!==strpos($class, MODEL_EXT))
			$res = self::load(APP_ROOT.DS.MODEL_EXT.DS.$class.PHP_EXT);
            
		return $res;
	}
	
    public static function obj($obj, $arg = null){
        !is_object(self::$_obj[$obj]) && self::$_obj[$obj] = new $obj(implode(',', $arg));
        return self::$_obj[$obj];
    }
    
	public static function run(){
		/*
        NFS::load(NFS_ROOT.'/base/Config.php');
		NFS::load(NFS_ROOT.'/base/Common.php');
		NFS::load(NFS_ROOT.'/base/NFSException.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		
		NFS::load(NFS_ROOT.'/base/Model.php');
        */
		//spl_autoload_register(array(self, 'autoload'));
		self::$controller = $controller = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']) : DEFAULT_CONTROLLER;
		self::$action = $act = !empty($_REQUEST['a']) ? strtolower($_REQUEST['a']) : DEFAULT_ACTION;
		$ctl = oo::c($controller);
		$ctl->$act();
		/*
		if(is_file($controllerFile)){
			require_once $controllerFile;
			try{
				$controller = new self::$controller();	
				method_exists($controller, $action) && $controller->$action();
			}catch (Exception $e){
				var_dump($e);
			}
		}else{
			exit($controllerFile.' not found');
		}
		*/
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



//NFS::load(NFS_ROOT.'/base/Cache.php');
//Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));
