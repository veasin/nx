<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/12 012
 * Time: 10:23
 */

//php -S 0.0.0.0:80 -t test\network
//http://127.0.0.1/server.php

if(!function_exists('getallheaders')){
	$this->data['header']=[];
	foreach($_SERVER as $name=>$value){
		if('HTTP_' === substr($name, 0, 5)) $this->data['header'][str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))]=$value;
	}
}
header('Content-type: application/json');
echo json_encode([
	'method'=>$_SERVER['REQUEST_METHOD'],
	'query'=>$_GET,
	'post'=>$_POST,
	'header'=>getallheaders(),
	'body'=>file_get_contents('php://input'),
	//'cookie'=>$_COOKIE,
	'file'=>$_FILES,
	//'content'=>file_get_contents($_FILES['f']['tmp_name']),
]);
