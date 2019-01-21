<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/3 003
 * Time: 10:59
 */

define('AGREE_LICENSE', true);

$loader = require('../vendor/autoload.php');

$r =new \nx\input();
var_dump($r['method']);
var_dump($r['header']);
var_dump($r['file']);
var_dump($r['input']);
var_dump($r['body']);
var_dump($r['query']);
var_dump($r['uri']);
var_dump($r['cookie']);

//var_dump($r->query('c'));
//var_dump($r->body('a'));
//var_dump($r->body('unknow'));
//var_dump($r->body());
//var_dump($r->method());
//var_dump($r->method('PUt'));
//echo '-------------', "\n";
//var_dump($r['query']['c']);
//var_dump($r['body']['a']);
//var_dump($r['body']['unknow']??null);
//var_dump($r['body']);
//var_dump($r['method']);

//echo '-------------', "\n";

//var_dump($r);

$this->in->body('a');
$this->in->query('a');
//$this->in['body']['a'];
//$this->in['query']['a'];
//
//$this->out['a']='a';
//$this->out->set('a', 'a');



