<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/8/31 031
 * Time: 09:40
 */
namespace demo;

use nx\db\pdo;
use nx\structure\output\rest;

define('AGREE_LICENSE', true);
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

$loader = require('../vendor/autoload.php');
$loader->addPsr4('demo\\', '.');
$setup =include 'setup.php';

$setup['router'][0] ='admin';
class app extends \nx\app{
	use \nx\structure\run\middleware, \nx\structure\control\controllers, pdo, rest;
}

$app=new app($setup);

//$app->config('xx.xx');
//$app->get('xx.xx');

//容器
/*
var_dump($app->get('demo.a'));
var_dump($app->get('demo.b'));
var_dump($app->container->get('demo.a'));
var_dump($app->container->get('demo.b'));
var_dump($app->container->get('demo.c'));
var_dump($app->container('file')->get('demo.b'));
*/


//日志
/*
$app->log('a info');
$app->logger->error('a error.');
$app->logger->warning('ok {date}');
$app->logger->warning('ok {date}', ['date'=>time()]);
$app->logger('debug');
$app->logger('file')->warning('xxx');
*/
//var_dump($app);

$app->run();
//$app->run(function(){
//	var_dump('ok');
//});
