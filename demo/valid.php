<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/06/06 006
 * Time: 10:12
 */

include __DIR__."/../src/autoload.php";
\nx\autoload::register([]);

$data=[
	'name'=>'vea',
	'password'=>'xx1xxx',
	'repassword'=>'xx1xxx',
	'idcard'=>'100000198103230000',
	'mobile'=>'17000000000',
	'phone'=>'010-81234567',
	'mail'=>'17000000000@qq.com',
	'qq'=>'17000000000',
	'code'=>'12345',
	'unknow'=>'中文',
	'date'=>'2017-3-3 15:27',
	'url'=>'http://192.168.31.102/nx/demo/valid.php',
	'ip'=>'192.168.31.102',
];

$valid=new \nx\helpers\validator($data, ['name'=>'用户名', 'password'=>'密码']);
$valid->add('name', $valid::not_empty)->add('name', $valid::min_length, 3);
$valid->add('name', $valid::max_length, 6);
$valid->add('name', $valid::length, 3);
$valid->add('password', $valid::equal, 'xx1xxx')->add('password', $valid::same, 'repassword');
$valid->add('code', $valid::numeric)->add('code', $valid::regex, '/^\d+$/');
$valid->add('unknow', $valid::chinese);
$valid->add('qq', $valid::qq);
$valid->add('mail', $valid::email);
$valid->add('phone', $valid::phone);
$valid->add('mobile', $valid::mobile);
$valid->add('idcard', $valid::id_card);
$valid->add('date', $valid::date);
$valid->add('url', $valid::url);
$valid->add('ip', $valid::ip)->add('ip');
$valid->add('content', $valid::not_empty, '');

$valid->add(['name'=>$valid::not_empty, ['name', $valid::min_length, 3], 'password'=>[$valid::equal, 'xx1xxx']]);

$data=$valid->start();
var_dump($data!==false ?$data :$valid->lastError());



