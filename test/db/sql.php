<?php

require '../../autoload.php';
\nx\autoload::register();

class sth{
	public function execute($params){
		echo json_encode($params);
	}
	public function rowCount(){return 0;}
	public function fetchAll(){return [];}

}
class db extends sth{
	public function prepare($sql){
		echo '<br><br>', $sql, '<br>';
		return new sth();
	}
}

trait test_db{
	public function db(){
		return new db();
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
$sql->where(['id'=>17])->delete();
EOT;
$sql->where(['id'=>17])->delete();


//$var =$sql->filter(['id'=>1])->readOne('titel');
//var_dump($var);

//$var =$sql->create(['titel'=>date('Ymd His'), 'interpret'=>'vea', 'jahr'=>idate('Y')]);
//var_dump($var);

//$var =$sql->filter($var)->readOne();
//var_dump($var);

