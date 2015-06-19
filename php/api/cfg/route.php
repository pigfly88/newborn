<?php
/**
 * 路由设置
 */
return array(
	/**
	 * URL模式
	 * 0 - 普通模式:?c=index&a=cate
	 * 1 - pathinfo模式:index/cate
	 */
	'ROUTE_TYPE'=>0,
	
	/**
	 * 在'ROUTE_TYPE'为0的时候启用
	 */
	'CONTROLLER_NAME'=>'c',
	'ACTION_NAME'=>'a',
);