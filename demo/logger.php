<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/24 024
 * Time: 13:43
 */

define('AGREE_LICENSE', true);

$loader =require('../vendor/autoload.php');

$logger =new \nx\logger(['dump'=>['\nx\logger\dump']]);

$logger->error('a error.');
$logger->warning('ok {date}');
$logger->warning('ok {date}', ['date'=>time()]);

$logger->register('file', '\nx\logger\file', ['path'=>'./logs/', 'uuid'=>'nx']);
$logger->info('all has this.');
$logger('file')->warning('xxx');

