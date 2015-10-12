<?php

/**
 * 预实现
 *
 * 1 尽可能兼容next                              (主要部分完成)
 * 2 支持composer                               (已完成)
 * 3 支持调度 使用route并闭包                     (已完成)
 *      a 同时多route匹配
 *      b 改造route数组为对象
 *      c 修改调度方式
 * 4 尽可能支持psr                               (psr2? psr3? psr4)
 * 5 支持命名空间                                (已完成)
 * 6 路由缓存                                   ()
 *  trait!!!                                   ()
 *  支持php新功能                               (??)
 *  脚手架(项目预制，数据缓存，模板编译...)        ()
 *  编译（打包）                                ()
 *  支持php7                                   ()
 *  路由 build(基于工作路径和解析)               ()
 */

/**
 * 问题
 *
 * 1 忽略文件读取差异（忽略文件io影响）             opcache
 *      a 配置文件                               include read < serialize read < json read <serialize write < json write
 *      b 缓存
 *      c 模板编译？
 * 2 autoload 冲突 ?（两套方案，nx本身支持（core，app，vendor?） & composer）
 *      a nx core loader
 *      b nx app loader
 *      c nx vendor loader ?
 *      独立加载速度优于合并批量处理。nx实现psr4,采用map方式
 * 3 env->app->router->control->mvc 层级太深？此为流程切割？？！！
 *      环境(->路由)->应用(->控制->mvc)
 *
 *      环境 = config
 *      路由 = uri & request
 *      应用 = code & run
 *
 *      环境（应用），请求，相应，辅助，代码规划
 * 4 只有对象可被传递并被子函数改变，变量需在声明处声明引用
 *
 *
 *
 *
 */

/**
 *  env->app->router->code()
 */

require 'autoload.php';
require 'o2.php';
require 'request.php';
require 'app.php';
$loader =nx\autoload::register([
	'nx\log\dump'=>__DIR__.'/log/dump.php',
	'nx\log\header'=>__DIR__.'/log/header.php',
	'nx\response\view'=>__DIR__.'/response/view.php',
	'nx\router\ca'=>__DIR__.'/router/ca.php',
	'nx\router\route'=>__DIR__.'/router/route.php',
	'nx\control\mvc'=>__DIR__.'/control/mvc.php',
	'nx\mvc\view'=>__DIR__.'/mvc/view.php',
	'nx'=>[__DIR__],
]);

