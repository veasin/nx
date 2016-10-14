<?php

include "../src/autoload.php";
\nx\autoload::register([]);

$queue =new \nx\queue();
$queue->append(function($result,$a,$b,$c){
	echo 'before 1', PHP_EOL;
	$result['num']=1;
	yield;
	$result['num']=10;
	echo 'after 1', PHP_EOL;
});
$queue->append(function($result,$a,$b,$c){
	echo 'before 2', PHP_EOL;
	$result['num']=2;
	yield;
	$result['num']=20;
	echo 'after 2', PHP_EOL;
});
$result =$queue->middleware(1,2,3);
var_dump($result);



$queue->append(function($result, $a, $b, $c){
	return $a.' 1';
});
$queue->append(function($result, $a, $b, $c){
	return $result.' 2';
});
$queue->append(function($result, $a, $b, $c){
	return $result.' 3';
});
$result =$queue->pipe(0, 1, 2, 3);
var_dump($result);
