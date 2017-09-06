<?php
namespace demo\controllers;

use nx\mvc\controller;

class index extends controller{
	public function getIndex(){
		$this->response['script']=basename($_SERVER['SCRIPT_FILENAME']);

		$this->response['links']=[
			''=>'默认首页',// index/index
			'?c=runorder'=>'控制器顺序',// runorder/index
			'?c=index&a=demo'=>'指定控制器和动作的演示',//ca路由的使用方法
		];
		$this->response['_file_'] ='links';//views/links.php
	}
	public function onDemo(){
		echo 'it\'s demo ~';
	}
	public function onDemo2(){
		//$this->response['ddd'] ='demo';
		$code =$this->request->get('code', 404, 'int');
		var_dump($code);
		$this->response->status($code);
	}
	public function onRoute($a='',$b='',$c=''){
		var_dump($a,$b, $c);
	}
}