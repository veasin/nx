<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/14 014
 * Time: 10:17
 */

namespace demo;

use nx\structure\control\controllers;
use nx\structure\output\rest;
use nx\structure\run\middleware;

define('AGREE_LICENSE', true);

$loader = require('../vendor/autoload.php');
$loader->addPsr4('demo\\', '.');
$loader->addPsr4('nx\\', '../nx');
$setup =include 'setup.php';

//$setup['router'][0] ='admin';
class app extends \nx\app{
	use middleware, controllers;
}

$app=new app($setup);
$app->out->setRender(function(\nx\output $out){
	$status =$out->buffer['status'] ?? 200;
	header($_SERVER["SERVER_PROTOCOL"].' '.$status);//HTTP/1.1
	header_remove('X-Powered-By');

	$headers =$out->buffer['header'] ?? [];
	$headers['Status']=$status;
	foreach($headers as $header=>$value){
		if(is_array($value)){
			foreach($value as $v){
				header($header.': '.$v);
			}
		}else header($header.': '.$value);
	}
	echo json_encode($out->get(), JSON_UNESCAPED_UNICODE);
});

//$app->out['user'] =['id'=>1, 'name'=>'vea'];
$app->out->buffer['status'] =201;
$app->out->put('user', ['id'=>1, 'name'=>'vea']);

//$x =$app->out['user'];//value is null notice
$app->out->get('user'); //value is null return null;

//$app->out->asArray();
//$app->out->get();
//$app->out->getAll();
//$app->out();//获取全部


//$app->out($data);//覆盖设置全部
//$app->out->overwrite($data);


//$app->out('render', $callback);//设置渲染方法
//$app->out->setRender($callback);//设置渲染方法

//$x =$app->out->render();
//var_dump($x);
//echo $app->out;
//$app->out =null;

//$app->out->setRender(function(\nx\output $out){
//	var_dump($out);
//});





//$out =new \nx\output();
//$out->setRender(function($out){//todo render part view
//	echo 'render';
//});
//$out->buffer['file'] ='xxx';
//$out['user'] =['id'=>1];
////$out=null;
//$part_view =(string)$out;
//var_dump($part_view);

//trit view
//$this->view($file, $data); =>$out




//1 buffer是否要提供对应方法
//2 register 是否要支持多(次)render渲染

//nx
//$out =[
//	'status'=>200,
//	'header'=>[],//cookie, file
//	'body'=>'',//file
//];

//$this->out['status']=201;


//$this->out->status(201);
//$this->out->header([]);
//$this->out->body;// => $this->data
//
//$this->out['user']=$user;
//$this->out->body['user']=$user;



//psr-7
//$out=[
//	'status'=>200,
//	'header'=>[],//cookie, file
//	'body'=>object(),//file
//];
//
//$body =new body();//string file
//echo Factory(200, [], $body);



//--------------------------------------------------------------

//$this->out['user'] =$user;
//register a out
//http ->body => out->body,


//$this->out->body['user'] =$user;


//------------------------

//$this->out =new \nx\out\view();
//$this->out->setFile();
//
//$this->out['__FILE__'];
//$this->out['__STATUS__'];
//$this->out['__HEADER__'];



//function(){
//	$this->out->render('view');
//	$this->out->file =$viewName;
//	function(){
//		$this->out['user'] =$user;
//		return $this->out->error('user-no-found');
//	}
//	$next();
//	if($this->out->error()) $error =$this->out->error();
//}
//
//function(){
//	$this->out->render('json');
//	try{
//		$next();
//		function(){
//			$this->out['user']=$user;
//
//			$this->throw('user-no-found');
//
//			function(){
//				$this->out['user']=$user;
//				//throw new User\NoFound\Exception();
//				function(){
//					$this->out['user']=$user;
//
//					$e =new \nx\exception();
//					$e->error ='user-no-found';
//					throw $e;
//				}
//			}
//		}
//	} catch(\nx\exception $e){
//		$error =$e->getError();
//		switch($error){
//			case 'user-no-found':
//				$this->out->buffer['status'] =404;
//		}
//	}
//	$this->out->status =201;
//}

/*
 * 访问地址 用户信息
 *
 * 中间件方法
 *
 * 1 如果是接口形式 - 使用json输出
 *  2 拦截错误
 *   3 获取用户信息
 *  2 自动转为对应状态
 * 1 输出
 *
 * 1 jsonRender(), 2 catchError2REST(), 3 getUser()
 *
 *
 * 1 如果是网页形式 - 使用模板输出
 *  2 根据路由确认模板 确认使用用户信息模板
 *   3 拦截错误
 *    4 获取用户信息
 *   3 转换错误到模板 确认使用错误模板
 *  2
 * 1 输出
 *
 * 1 viewRender(), 2 matchView() 3 catchError2HTML(), 4 getUser()
 *
 *
 * getUser()
 * 错误状态 error-args =>400  //new Exp()
 * 1 我们要不要定义统一错误类型
 * 2 错误的上报方式 ： 错误编码 or 新错误异常
 *
 * matchView()
 * view-file-name
 * 1 临时数据的缓存 错误编码应该算是临时数据
 *
 * 当用户不存在时候
 *   接口模式 只要返回不存在 自动转换为 404状态
 *   页面模式 需要区分找不到的是什么 同时 还需要输出错误描述“用户不存在” 这个在什么时候定义
 *   getUser() 代码应该相同 即代码中包含“错误”和“描述”
 *     多种异常 or 相同异常不同原因
 *     分为业务异常 和 框架异常404 500
 *
 *     - 1 固定错误类型种类 不同错误原因 404,user 404,article  1) 错误的状态 2) 业务的种类
 *     + 2 不同错误类型 不同错误原因 user-no-found article-no-found (根据业务走) 输出的时候自动替换输出字符内容(翻译) user-401 user-404 user-500 user-xxx 每种业务状态都需要添加一个新标识
 *
 *     1 return $this->out->error();
 *     2 throw new Exception()
 *     3 throw new Custom\Exception()
 *
 * $this->request 固定不能修改
 * $this->out 数组or某个对象？
 *
 */

//$this->out['user'] =$user;
//$this->out->buffer['file'] =$viewName;
//$this->out->buffer['error'] =400;

