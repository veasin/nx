<?php

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

require '../src/autoload.php';//框架自动加载路径，可使用composer替换
nx\autoload::register([]);//自动加载注册，可在其中指定命名空间第一段指向目录

class app extends \nx\app{//框架的根基是trait 需要先use，如果没有use任何的，默认为404直接报错

}

app::factory()->run();

