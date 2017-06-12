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
$valid->add('name', $valid::not_empty)->add('name', [$valid::min_length, 3]);
$valid->add('name', [$valid::max_length, 6], [$valid::length, 3]);
$valid->add('password', [$valid::equal, 'xx1xxx'])->add('password', [$valid::same, 'repassword']);
$valid->add('code', $valid::numeric)->add('code', [$valid::regex, '/^\d+$/']);
$valid->add('unknow', $valid::chinese);
$valid->add('qq', $valid::qq);
$valid->add('mail', $valid::email);
$valid->add('phone', $valid::phone);
$valid->add('mobile', $valid::mobile);
$valid->add('idcard', $valid::id_card);
$valid->add('date', $valid::date);
$valid->add('url', $valid::url);
$valid->add('ip', $valid::ip)->add('ip');
$valid->add('content', [$valid::not_empty, '']);

$r=$valid->check();
var_dump($r!==false ?$r :$valid->lastError());

$valid2=new \nx\helpers\validator('post', ['name'=>'用户名', 'password'=>'密码']);
$valid2->add(['name', $data], $valid2::not_empty, [$valid2::max_length, 6]);
$valid2->add(['name'=>'pname', 'post'], $valid2::not_empty);
$valid2->add(['name'=>'gname', 'get'], $valid2::not_empty);
$valid2->add(['code', 'header'], $valid2::not_empty);
$valid2->add('mobile', $valid2::not_empty);//default:post
$r=$valid2->check();
var_dump($r!==false ?$r :$valid2->lastError());

$valid3=new \nx\helpers\validator($data);
$r=$valid3->add('code', \nx\helpers\validator::not_empty, [\nx\helpers\validator::id])->check('code');
var_dump($r!==false ?$r :$valid3->lastError());