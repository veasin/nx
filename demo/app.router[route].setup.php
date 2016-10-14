<?php

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

require '../src/autoload.php';//框架自动加载路径，可使用composer替换
nx\autoload::register([]);//自动加载注册，可在其中指定命名空间第一段指向目录

class app extends \nx\app{
	use \nx\router\route;
}
$app =app::factory([
	'router/route'=>[//不同trait需要的配置是不同的
		'rules'=>[
			['*', '', function(){// xxxx.php
				echo 'hello world !!';
			}],
			['get', 'hello', function(){// xxxx.php/hello
				echo 'hello ~~~~';
			}],
			['get', '$num/(?P<num>\d+)/say/(?P<any>.+)', function (\nx\request $request){// xxxx.php/num/789/say/any  output:789:any
				echo $request->params('num'), ':', $request->params('any');
			}],
			['get', 'args', function (\nx\request $request){// xxxx.php/args?a=1&b=2  output:array(2) { ["a"]=> string(1) "1" ["b"]=> string(1) "2" }
				var_dump($request->arg());
			}],
		],
	],
]);
$app->get('word', function(){//并存
	echo 'world !!';
});
$app->run();

