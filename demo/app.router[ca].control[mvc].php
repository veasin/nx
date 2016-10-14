<?php
namespace demo;

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

require '../src/autoload.php';//框架自动加载路径，可使用composer替换
\nx\autoload::register([
	'demo'=>['.'],//声明命名空间为当前目录
]);//自动加载注册，可在其中指定命名空间第一段指向目录

// \demp\app
class app extends \nx\app{
	use \nx\router\ca,  //使用 c a 路由
		\nx\control\mvc,//使用mvc类控制代码
		\nx\log\header,//使用网页头输出log日志
		\nx\response\web, //响应为网页方式
		\nx\response\view;//响应为模板输出
}

app::factory([])->run();
