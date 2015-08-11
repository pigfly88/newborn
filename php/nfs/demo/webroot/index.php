<?php
/**
 * 项目入口文件
 * 加载NFS初始化文件，加载配置、基类等等
 *
 */

define('APP_ROOT', dirname(__DIR__));

require '../../framework/NFS.php';

NFS::run();


