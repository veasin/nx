<?php

require '../../autoload.php';
\nx\autoload::register();

class app extends \nx\app{
	use \nx\db\pdo,
		\nx\db\table;
	var $setup =[
		'db.pdo'=>[
			'default'=>[
				'dsn'=>'mysql:dbname=cdcol;host=127.0.0.1;charset=UTF8',
				'username'=>'root',
				'password'=>'',
				'options'=>[],
			],
		],
	];
}

$app =new app();

$sql =$app->table('cds', 'id');
$var =$sql->read();
var_dump($var);

$var =$sql->filter(['id'=>1])->readOne('titel');
var_dump($var);

$var =$sql->create(['titel'=>date('Ymd His'), 'interpret'=>'vea', 'jahr'=>idate('Y')]);
var_dump($var);

$var =$sql->filter($var)->readOne();
var_dump($var);

