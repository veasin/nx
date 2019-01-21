<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/3 003
 * Time: 10:59
 */

define('AGREE_LICENSE', true);

$loader = require('../../vendor/autoload.php');

$request =new \nx\input();
//var_dump($request['header']);

/**
 * method : post
 * uri : demo.php?b=1&c=2&d=3
 * body : a=1&b=2
 * header : Content-Type=application/x-www-form-urlencoded;
 */


//trait
//$f =$app->filter()->query('a')->filter('int');
//$f =$app->filter('a', 'query')->filter('int');

$f =new \nx\filter\filter( ['request'=>$request, 'from'=>'query']);

//->key('key', 'from')->filter();

$x =$f->key('content-length', 'header')->filter('int');
var_dump($x);
$x =$f->key('b', 'query')->filter('int');
var_dump($x);
$x =$f->key('b', 'body')->filter('int');
var_dump($x);
$x =$f->key('b')->filter('int');
var_dump($x);
$x =$f->key('b', 'unknow')->filter('int');
var_dump($x);

echo '-------------------', "\n";

//->from('key')->filter();

$x =$f->body('b')->filter('int');
var_dump($x);
$x =$f->query('b')->filter('int');
var_dump($x);

$x =$f->unknow('a')->filter('int');
var_dump($x);

//$f->header('host')->filter('int');
//$f->body('host')->filter('int');
//$f->query('host')->filter('int');
//$f->uri('host')->filter('int');
//$f->cookie('host')->filter('int');
//
//$f->header('host');
//$f->header('host', 'int');
//$f->body('host', 'int', '!empty');
//

echo '-------------------', "\n";

// = $this->key('b')

$x =$f['b']->filter('int');
var_dump($x);


$user=['id'=>'3'];
$f->addSource('user', $user);
$x =$f->user('id')->filter('int');
var_dump($x);

$f->addRule('custom', function(){});

exit();


/*try{
	$x=$f->get('content-type', '!null');
	var_dump($x);
//} catch(\Exception $e){
	//var_dump($e->getKey().':'.$e->getMessage());
} catch(\nx\exception\filter\rule $e){
	var_dump($e->getKey().':'.$e->getMessage());
}*/

//$x =$f->get('dnt', '!empty', 'int');
//var_dump($x);
//
//$x =$f->get('accept-language', 'array');
//var_dump($x);
//
//$x =$f->get('host', 'ip');
//var_dump($x);
//
//
//$x =$f->key('host', 'input')->check('ip', 'int', '!empty');
//var_dump($x);
//
//$x =$f->var($request['input']['host'])->check('ip', 'int', '!empty');
//var_dump($x);
//
//$x =$f->var($xx)->check('ip', 'int', '!empty');
//var_dump($x);


//--------------------------------

$x =$f->from('header', 'host')->check('int');

$x =$f->post('host', 'int');
$x =$f->input('host', 'int');
$x =$f->get('host', 'int');



$x =$f->key('host');
$x1 =$f->key('host1');
$x2 =$f->key('host2');
$xx =$x->check();

//$f =$this->filter('params');

//$host =$f->key('host')->get();
$host =$f->key('host')();
var_dump($host);

$id =$f->key('host')->check('!null', 'int');
var_dump($id);

//$post =$this->filter('post');
$host =$f['host']('int');
var_dump($host);

$host =$f['host']->check('int');
var_dump($host);

$host =(string)$f['host'];
var_dump($host);

//$id =$post['uid']('!null', 'int');

//$post['uid']  //callable //value



