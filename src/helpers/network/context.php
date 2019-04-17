<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/11 011
 * Time: 17:08
 * 尽量简化，链式调用，请求响应分离，方法分离，通过phpunit
 * 请求：query,header,cookie,body
 * 响应: statusCode, header, body
 */
declare(strict_types=1);
namespace nx\helpers\network;

class context{
	public static function request(string $uri, string $method='get'):context\request{
		return new context\request($uri, $method);
	}
	public static function get(string $uri, array $query=[]):context\request{
		return (new context\request($uri, 'get'))->query($query);
	}
	public static function post(string $uri, $data='', array $query=[]):context\request{
		return (new context\request($uri, 'post'))->query($query)->body($data);
	}
	public static function put(string $uri, $data='', array $query=[]):context\request{
		return (new context\request($uri, 'put'))->query($query)->body($data);
	}
	public static function patch(string $uri, $data='', array $query=[]):context\request{
		return (new context\request($uri, 'patch'))->query($query)->body($data);
	}
	public static function delete(string $uri, array $query=[]):context\request{
		return (new context\request($uri, 'delete'))->query($query);
	}
	public static function head(string $uri, array $query=[]):context\request{
		return (new context\request($uri, 'head'))->query($query);
	}
	public static function options(string $uri, array $query=[]):context\request{
		return (new context\request($uri, 'options'))->query($query);
	}
}

//context::post('xxx', ['a'=>1])->contentType('json')->send();
//context::post('xxx')->body('123', type::JSON)->send();

//todo contentType Authorization file
/*
try{
$r =stream::factory('http://xxx.com', 'post')
	->method('get')
	->uri('get')
	->cookie('get')
	->header('get')
	->send();

}catch(\Exception $e){

}

$r =stream::post('http://xxx.com')->send();
$r =stream::get('http://xxx.com')->send();


$s =new stream('http://xxx.com', 'post');
$s->method('get');
$r =$s->send();

if($r->status() ===200) $arr =$r->json();

$html =$r->body();
$header =$r->header('xxx');

$s->with


if($request->method('post')){

}

$method ='put';



$method=null;







	$s->method($method)->send();


*/





