<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/09/15 015
 * Time: 11:32
 */



define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

require '../src/autoload.php';//框架自动加载路径，可使用composer替换
nx\autoload::register();


class app1 extends \nx\app{
	use \nx\log\redis, \nx\router\route;
}

class app2 extends \nx\app{
	use \nx\log\file, \nx\router\route;
}

$setup=[
	'log/redis'=>[
		'connect'=>[
			'host' => '127.0.0.1',
			'port' => '6379',
			'auth' => '',
			'select' => 2,
			'timeout' =>3,
		],
	],
	'log/file'=>[
		'path'=>__DIR__.'/logs/',
	]
];



$app1 =app1::factory($setup);
$app1->on('', function(){
	$start =microtime(true);
	for($i=0; $i<10000; $i++){
		$this->log("0123456789abcdefghijklmnopqrstuvwxyz");
	}
	echo 'app[redis]', ':', microtime(true) -$start, '<br>';
});
$app1->run();


$app2 =app2::factory($setup);
$app2->on('', function(){
	$start =microtime(true);
	for($i=0; $i<10000; $i++){
		$this->log("0123456789abcdefghijklmnopqrstuvwxyz");
	}
	echo 'app[ file ]', ':', microtime(true) -$start;
});
$app2->run();


/**
 * app[redis]:0.49558305740356
 * app[ file ]:0.18205690383911
 */