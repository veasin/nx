<?php

require '../../autoload.php';
\nx\autoload::register();


trait test_db{
	public function insert($sql, $params=[], $config='default'){
		echo '<br><br>[',$config, ']', $sql, '<br>', json_encode($params);
	}
	public function select($sql, $params=[], $config='default'){
		echo '<br><br>[',$config, ']', $sql, '<br>', json_encode($params);
	}
	public function execute($sql, $params=[], $config='default'){
		echo '<br><br>[',$config, ']', $sql, '<br>', json_encode($params);
	}
}

class app extends \nx\app{
	use test_db,
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

echo '<br><br>init:', <<<'EOT'
$app =new app();
$sql =$app->table('cds', 'id');
EOT;
$app =new app();
$sql =$app->table('cds', 'id');


$sql->create([
	['titel'=>1, 'interpret'=>2],
	['titel'=>2, 'interpret'=>2],
	['titel'=>3, 'interpret'=>2],
]);



echo '<br><br>>call:', <<<'EOT'
$sql->read();
EOT;
$sql->read();

echo '<br><br>>call:', <<<'EOT'
$sql->create(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);
EOT;
$sql->create(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);

echo '<br><br>>call:', <<<'EOT'
$sql->where(14)->update(['interpret'=>'vea']);
EOT;
$sql->where(14)->update(['interpret'=>'vea']);


echo '<br><br>>call:', <<<'EOT'
$sql->where(15)->where(['id'=>16], 'or')->update(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);
EOT;
$var =$sql->where(15)->where(['id'=>16], 'or')->update(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);

echo '<br><br>>call:', <<<'EOT'
$var =$sql->where(15)->where('id', 16, '=', 'or')->update(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);
EOT;
$var =$sql->where(15)->where('id', 16, '=', 'or')->update(['interpret'=>'veas', 'titel'=>date('Y-m-d H:i:s')]);


echo '<br><br>>call:', <<<'EOT'
$sql->where('id', 13, '=')->read();
EOT;
$sql->where('id', 13, '=')->read();

echo '<br><br>>call:', <<<'EOT'
$sql->where('id', 13, '=')->read('titel', 'interpret');
EOT;
$sql->where('id', 13, '=')->read('titel', 'interpret');

echo '<br><br>>call:', <<<'EOT'
$sql->where('id', 13, '=')->read(['titel', 'interpret']);
EOT;
$sql->where('id', 13, '=')->join('book b', ['titel'])->read(['b.titel', 'interpret']);

echo '<br><br>>call:', <<<'EOT'
$sql->where(['id'=>17])->delete();
EOT;
$sql->where(['id'=>17])->delete();


//$var =$sql->filter(['id'=>1])->readOne('titel');
//var_dump($var);

//$var =$sql->create(['titel'=>date('Ymd His'), 'interpret'=>'vea', 'jahr'=>idate('Y')]);
//var_dump($var);

//$var =$sql->filter($var)->readOne();
//var_dump($var);

