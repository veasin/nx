<?php
$app_tpl=<<<ETO
<?php
namespace {name};

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区
\$config =include("./setup.php");

require '../../src/autoload.php';//框架自动加载路径，可使用composer替换
\\nx\autoload::register([
	'{name}'=>['.'],//声明命名空间为当前目录
]);//自动加载注册，可在其中指定命名空间第一段指向目录

class app extends \\nx\\app{
	{uses}
	public \$path =__DIR__;
}
app::factory(\$config)->run();
ETO;
$app_tpl2=<<<ETO
<?php
namespace {name};

class app extends \\nx\app{
	{uses}
	public \$path =__DIR__;
}
ETO;
$web_tpl=<<<ETO
<?php
namespace {name};

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区
\$config =include("../../setup.php");

require '../../src/autoload.php';//框架自动加载路径，可使用composer替换
\\nx\autoload::register([
	'{name}'=>['.'],//声明命名空间为当前目录
]);//自动加载注册，可在其中指定命名空间第一段指向目录
app::factory(\$config)->run();
ETO;
$controller_tpl=<<<ETO
<?php
namespace {name}\controllers;

use nx\mvc\controller;

class {control} extends controller{
	public function getIndex(){
		echo 'com in';
	}
}
ETO;
$model_tpl=<<<ETO
<?php
namespace {name}\models;

use nx\db\pdo;
use nx\db\\table;

class {model} extends \\nx\mvc\model{
	use pdo, table;

	public function testModel(){
		echo 'com in';
	}
}
ETO;
$view_tpl=<<<ETO
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	com in
</body>
</html>
ETO;
