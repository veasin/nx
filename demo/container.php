<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/24 024
 * Time: 13:25
 */

define('AGREE_LICENSE', true);

$loader =require('../vendor/autoload.php');

$container =new \nx\container();
var_dump($container->get('xx'));

$container->set('xx', 'xx');
var_dump($container->get('xx'));

$container->register('config', '\nx\container\config', ['path'=>'./config/'] );
var_dump($container->get('demo.a'));

var_dump($container('config')->get('demo.b'));
