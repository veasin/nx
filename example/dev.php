<?php
error_reporting(E_ALL);
ini_set( 'display_errors', 'On' );

include "../vendor/autoload.php";

$ms =microtime(true);
$mem= memory_get_usage();

class app extends \nx\app{
	use \nx\parts\runtime;
	protected function main(){
		//$x =$this->in?->body();
		//var_dump($_SERVER['REQUEST_METHOD'] ?? 'cli');
		//var_dump($_SERVER['REQUEST_URI'] ?? 'cli');
		//var_dump($_SERVER['argv']);
		//var_dump($this->in['params']);
		//var_dump($this->in['uri']);
		var_dump($this->in->body());
	}
}

$app =new app();
$app->run();

var_dump(microtime(true) -$ms, memory_get_usage()-$mem);