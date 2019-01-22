<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/9/3 003
 * Time: 16:53
 */

//$log =new \nx\log\console();

return [
	'logger'=>[
		'file'=>['\nx\logger\file', [
			'path'=>__DIR__.'/logs',
			//'path'=>'var/log/',
			//'file'=>'demo.log',
			'level'=>[
				//'error'=>'error.log',
				//'notice'=>false,
				//'debug'=>'debug.log',
			]
		]],
		//['\nx\logger\dump'],
		//['\nx\log\console'],
	],
	'container'=>[
		'build'=>['\nx\container\build', [
			'pdo'=>['\pdo', 'mysql:dbname=mysql;host=127.0.0.1;charset=utf8mb4', 'root', ''],
			'pdo2'=>'\pdo',
			'pdo3'=>function(){},
			//'pdo4'=>object(),

		]],
		'config'=>['\nx\container\config', [
			//'path'=>'config',
		]],
	],
	'db'=>['default',
		'default'=>['\nx\db\pdo', [
			'dsn'=>'mysql:dbname=mysql;host=127.0.0.1;charset=utf8mb4',
			'username'=>'root',
			'password'=>'',
			'options'=>[],
		]],
		'pdo'=>['\pdo', 'mysql:dbname=mysql;host=127.0.0.1;charset=utf8mb4', 'root', '',[],],
	],
	'router'=>['default',
		'default'=>['\nx\router\route', [
			'uri'=>$_SERVER['PATH_INFO']??'',
			'method'=>strtolower($_SERVER['REQUEST_METHOD']),//当前页面请求的method
			'rules1'=>[
				['*', '*',
					[null, function(...$args){
						var_dump($args);
						echo '1', "\n";
						//return null;
						//return 456;
						////return false;
						//$r =$next(123);//----------------------stack()->next()->no next->back()
						$r =yield 123;
						echo '1a', $r, "\n";
						$r =yield 456;
						echo '1b', $r, "\n";
						//$r =yield 789;
						//echo '1c', $r, "\n";
					}],
					[null, function(...$args){
						var_dump($args);
						//if(true) return 2;
						//$this->args;
						//$this->getBack();
						echo '2', "\n";
						$r =yield 't2';
						echo '2a', $r, "\n";
						$r =yield 789;
						echo '2b', $r, "\n";
						//return false;
					}]
				],

				['*', 'uri', [null, function(...$args){
					var_dump($args);
					echo '3', "\n";
					$r =yield 'to4';//----------------------stack()->next()->no next->back()
					echo '3',$r, "\n";
					//return null;
					//return false;
					//return any;
					//yield false;
					//var_dump('3b');
				}]],
				['*', 'uri', [null, function(...$args){
					var_dump($args);
					echo '4', "\n";
					yield;
					echo '4b', "\n";
					return 5;
				}]],

				['*', 'uri1', ['more', 'apps3'], '动作组名'],
			],
			'rules2'=>[
				'*'=>[
					'action1','action1','action1','action1','action1',
				],
				'/api'=>['action2'],
				'/user'=>[
					'post'=>['action3'],
					'get'=>['action4'],
				],
			],
			'rules'=>[
				['*', '*',
					[null, function($next, ...$args){
						//var_dump($args);
						echo '1', "\n";
						//return null;
						//return 456;
						////return false;
						//$r =$next(123);//----------------------stack()->next()->no next->back()
						$r =$next(123);
						echo '1a<', $r, "\n";
						$r =$next(456);
						echo '1b<', $r, "\n";
						//$r =yield 789;
						//echo '1c', $r, "\n";
					}],
					[null, function($next, ...$args){
						//var_dump($args);
						if(true) return 2;
						//$this->args;
						//$this->getBack();
						echo '  2', "\n";
						$r =$next('t2');
						echo '  2a<', $r, "\n";
						$r =$next(789);
						echo '  2b<', $r, "\n";
						//return false;
					}]
				],

				['*', 'uri', [null, function($next, ...$args){
					//var_dump($args);
					echo '    3', "\n";
					$r =$next('to4');//----------------------stack()->next()->no next->back()
					echo '    3<',$r, "\n";
					return '3a';
					//return null;
					//return false;
					//return any;
					//yield false;
					//var_dump('3b');
				}]],
				['*', 'uri', [null, function($next, ...$args){
					//var_dump($args);
					echo '      4', "\n";
					$next();
					echo '      4a<', "\n";
					return 5;
				}]],

				['*', 'uri1', ['more', 'apps3'], '动作组名'],
			],
			'actions'=>[
				'动作组名'=>[
					['user','check'],
					['user','check','参数'],
					['\api\user','check','参数', '参数2', '参数3'],
					//[$this, 'main', '参数', '参数2'],
					[null, function(){},'参数', '参数2'],//null => 绑定对象
				],
			],
		]],
		'admin'=>['\nx\router\route', [
			'rules'=>[//admin/1/do/2
				['*', '*',['index', 'error'], ['index', 'demo1']],
				['*', '/admin/:id+',['index', 'error'], ['index', 'demo1']],
				['*', '/admin/:id/do/:uid',['index', 'error'], ['index', 'demo1']],
				['*', '/admin/:name/do/:uuid',['index', 'error'], ['index', 'demo1']],
			],
		]],
	],
	'cache'=>[
		'default'=>['redis', []],
	],
	'demo.a'=>'b',
	'demo.b'=>456,
];

//function app1($next){
//	//todo xxx
//	$next();
//	yield;
//	//todo xxx
//}
