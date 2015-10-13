<?php

require '../../autoload.php';
\nx\autoload::register();

class app extends \nx\app{
	use \nx\db\pdo,
		\nx\db\table;
	var $setup =[
		'db.pdo'=>[
			'default'=>[
				'dsn'=>'mysql:dbname=vzhen;host=127.0.0.1;charset=UTF8',
				'username'=>'root',
				'password'=>'',
				'options'=>[],
			],
		],
	];
}

$app =new app();

$sql =$app->table('chat', 'id');

$var =$sql->filter(5)->readOne();

var_dump($var);

