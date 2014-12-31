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

class NFS{
	protected static $_loaded;
	
	public static $controller;
	public static $action;	
	public static $approot;
	
    protected static $_obj;
    
    //加载文件
    public static function load($file=''){
		if(empty($file)){
            return self::$_loaded;
        }elseif(!is_file($file)){
            return false;
        }elseif(!isset(self::$_loaded[$file])){
            self::$_loaded[$file] = include($file);
        }
        return self::$_loaded[$file];
	}
	
    //这个文件加载过了吗？
	public static function loaded($file){
		return true===self::$_loaded[$file] ? true : false;
	}
	
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
        NFS::load(NFS_ROOT.'/base/Config.php');
		NFS::load(NFS_ROOT.'/base/Common.php');
		NFS::load(NFS_ROOT.'/base/NFSException.php');
		NFS::load(NFS_ROOT.'/base/Component.php');
		NFS::load(NFS_ROOT.'/base/Controller.php');
		NFS::load(NFS_ROOT.'/base/Model.php');
        
		//spl_autoload_register(array(self, 'autoload'));

		self::$controller = !empty($_REQUEST['c']) ? strtolower($_REQUEST['c']).CONTROLLER_EXT : DEFAULT_CONTROLLER.CONTROLLER_EXT;
		$action = self::$action = !empty($_REQUEST['a']) ? strtolower($_REQUEST['a']) : DEFAULT_ACTION;
		$controllerFile = APP_ROOT.DS.CONTROLLER_FOLDER_NAME.DS.self::$controller.PHP_EXT;
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
		/**
		 * 通用方法调度
		 * 应付普通的增删改查功能
		 * 表名和字段经过加密之后放到表单，这边会解析出来，加密的token在配置文件中设置
		 */
		if(substr($action, 0, 2) == str_repeat(SEPARATOR, 2)){
			//调度前执行before方法
			$action_before = $action.SEPARATOR.BEFORE;
			method_exists($controller, $action_before) && $controller->$action_before();
			
			list($func, $table) = explode(SEPARATOR, substr($action, 2));
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
				$m->$func($data);
			}
			
			//调度后执行after方法
			$action_after = $action.SEPARATOR.AFTER;
			method_exists($controller, $action_after) && $controller->$action_after($__res);
		}
	}
	
    /**
     * 利用魔术方法的特性动态加载NFS下的类库
     * e.g:
     * NFS::helper('Socket')，helper是未定义的静态方法，那么就会通过__callStatic()去调度helper文件夹的Socket类
     */
	public static function __callStatic($folder, $arg) {
        if(!is_object(self::$_obj[$arg[0]])){
        	$class = explode('/', $arg[0]);
            self::load(NFS_ROOT.$folder.DS.$arg[0].PHP_EXT);
            $arg = null && count($class)>1 && $arg = implode(',', array_slice($class, 1));
            self::$_obj[$arg[0]] = new $class[count($class)-1]($arg);
        }
        
        return self::$_obj[$arg[0]];
    }
   
   
}



//NFS::load(NFS_ROOT.'/base/Cache.php');
//Cache::init(NFS::load(CORE_ROOT.'/config/cache.php'));
