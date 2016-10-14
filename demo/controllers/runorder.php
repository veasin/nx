<?php
namespace demo\controllers;

use nx\mvc\controller;

class runorder extends controller{

	public function before(){
		echo '1.before', '<br>', PHP_EOL;
	}
	public function beforeIndex(){
		echo '2.beforeIndex', '<br>', PHP_EOL;
	}
	public function getIndex(){
		echo '3.getIndex', '<br>', PHP_EOL;
	}
	public function postIndex(){
		echo '3.postIndex', '<br>', PHP_EOL;
	}
	public function onIndex(){
		echo '4.onIndex', '<br>', PHP_EOL;
	}
	public function afterIndex(){
		echo '5.afterIndex', '<br>', PHP_EOL;
	}
	public function after(){
		echo '6.after', '<br>', PHP_EOL;
	}

}