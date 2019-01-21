<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 11:21
 */

namespace demo\controllers;

use nx\structure\callApp;

class index{
	use callApp;
	public function error($next){
		try{
			$next();
		}catch(\nx\exception $e){
			switch($e->getCode()){
				case 401:
					$this->out->buffer['status'] =401;
					//var_dump(401);
					break;
			}
		}
	}

	public function demo1(){
		//(function(){
		//	throw new \Exception('xxxx', 401);
		//})();

		//$this->out['xx'] ='xx';

		$user =$this->table('user');
		$this->out['user'] =$user->read();

		//$db =$this->db();
		//$this->out['user'] =$db->select('select * from user1 limit 10');

		$r =null;
		$this->out['xx'] = $r ?$r :'false';

		$this->out['userx'] =null ?? [123];


		//[$id, $iv, $encrypted_data]=$f(['from'=>'input', 'throw'=>401])->addRule('>', function($v, $check){
		//	return $v>$check;
		//})->check([
		//	'id'=>[0, 'int', ['>', 0, 'throw'=>400], 'from'=>'params', 'default'=>0, 'throw'=>401],
		//	'iv'=>['', 'not_empty', '!empty', 'empty'],
		//	'encrypted_data'=>['', 'not_empty'],
		//	'from_ids'=>[[], 'json', 'from'=>'post'],
		//	'status'=>[false, 'int'],
		//	'page'=>[1, 'int'],
		//	'max'=>[1, 'int'],
		//]);
		//var_dump($id, $iv, $encrypted_data);
		//
		//$id=$this->request->params('id', 0, 'int', ['>', 0, 'throw'=>401], ['throw', 401]);


		//$id=$this->request->params('id', 0, 'int');
		//$iv=$this->request->input('iv', '', 'str');
		//$encrypted_data=$this->request->input('encrypted_data', '', 'str');
		//if(!empty($encrypted_data) && !empty($iv)){
	}
	public function demo2(){
		$this->log('demo1');
		echo 'demo1';
	}
	//public function token(){
	//	//取值-从指定地方-按照指定规则
	//	$this->filter('header', [])
	//		->addRule()
	//		->get('key', [], [], [])
	//		->more([
	//			'key'=>[[], [], []],
	//			'key2'=>[[], [], []],
	//		]);
	//
	//
	//	//$this->contrainer->set('check', $this->valid('header'));
	//
	//	$f =$this->contrainer->get('values', 'header');
	//	//$f =$this->request;
	//
	//	$f =$this->valid('header');
	//
	//	[$debug, $id] =$f([
	//		'debug'=>[['int', 0], ['>', 0]],
	//		'uid'=>['int', '_token'],
	//	]);
	//
	//	$id =$f->get('debug', 'int') ?? 0;
	//
	//	[,$token] =$this->valid('header', ['throw'=>404])->check([
	//			'debug'=>['int', ['>', 0]],
	//			'uid'=>['int', '_token'],
	//		]);
	//	$this->valid()->header('debug', 'int', ['>', 0]);
	//	$this->valid('get')->get('debug', 'int', ['>', 0]);
	//	$this->valid('header')->debug('int', ['>', 0]);
	//	$this->valid('params')->id('int', ['>', 0]);
	//	$id =$this->valid('params')->id('int');
	//
	//	$x =$this->valid('get')->get('phone', '!null', 'num');
	//	if(null ===$x) throw new \Exception('num');
	//
	//
	//
	//	$this->response($token??'no token');
	//
	//	if(0 ==$this->request->header('debug', 0, 'int')) return $this->response->status(404);//未发送debug头，直接失败
	//	$id =$this->request->get('uid', 0, 'int');//用户id
	//
	//	$amc=\api\models\customer::instance();
	//	$this->response($amc->getToken($id));
	//}
}