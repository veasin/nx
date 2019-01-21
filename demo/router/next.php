<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/26 026
 * Time: 15:16
 *
 *      __  ___  ___
 *     /  \ \  \/  /
 *    /  / \ \  \ /
 *   /  /\  \ \  \
 *  /  /  \  / \  \
 * /__/    \/__/\__\
 *        ___  ___
 *     __ \  \/  /
 *    /  \ \  \ /
 *   /  / \ \  \
 *  /  /\  / \  \
 * /__/  \/__/\__\
 *        ___  ___
 *     __ \  \/  /
 *    /  \ \ /  /
 *   /    \ /  /
 *  /  /\  /  / \
 * /__/  \/__/\__\
 *         ___  ___
 *         \  \/  /
 *     __   \  \ /
 *    /  \   \  \
 *   /    \ / \  \
 *  /  /\  /  /\  \
 * /__/  \/__/  \__\
 *       ___  ___
 *      _\  \/  /
 *     /  \  \ /
 *    /    \  \
 *   /  /\  \  \
 *  /  / /\_/\  \
 * /__/ /__/  \__\
 *           ________
 *      /\   \/ \/  /
 *     /  \  /\ /\ /
 *    /    \/  \  /
 *   /  /\    / \  \
 *  /  /  \  /  /\  \
 * /__/    \/__/  \__\
 *           ______    ___
 *      __  /  /\  \  /  /
 *     /  \/  /  \  \/  /
 *    /      /    \    /
 *   /  /\__/     /    \
 *  /  /         /  /\  \
 * /__/         /__/  \__\
 *             ______    ___
 *      /\    /  /\  \  /  /
 *     /  \  /  /  \  \/  /
 *    /    \/  /    \    /
 *   /  /\    /     /    \
 *  /  /  \  /     /  /\  \
 * /__/    \/     /__/  \__\
 *  ___    __ ___    ___
 * |   \  |  |\  \  /  /
 * |    \ |  | \  \/  /
 * |     \|  |  \    /
 * |  |\     |  /    \
 * |  | \    | /  /\  \
 * |__|  \___|/__/  \__\
 * ___        ________________
 * \  \      /  /  ______/    \
 *  \  \    /  /  /_____/  /\  \
 *   \  \  /  /  ______/  /__\  \
 *    \  \/  /  /_____/  ______  \
 *     \____/________/__/      \__\
 */

define('AGREE_LICENSE', true);

$loader = require('../../vendor/autoload.php');
$setup =include '../setup.php';
class app extends \nx\app{
	use \nx\structure\run\middleware, \nx\structure\control\controllers;

}
$setup['router']['default'][1]['uri'] ='uri';
$app=new app($setup);


//class control{
//	private $stack=[];
//	/**
//	 * @var \nx\app
//	 */
//	private $app =null;
//	public function __construct($app){
//		$this->app =$app;
//	}
//	public function next(){
//		foreach($this->app->router->next() as $call =>$args){
//			if(is_callable($call)){
//				yield call_user_func_array($call, $args);
//			} elseif($call[1] instanceof \Closure){
//				yield call_user_func_array($call[1]->bindTo($call[0] ?? $this->app), $args);
//			}
//		}
//		while($generator=array_pop($this->stack)){
//			$generator->next();
//		}
//	}
//	public function control(){
//		foreach($this->next() as $call =>$args){
//			$generator =call_user_func_array($call, $args);
//
//			if($generator && $generator instanceof \Generator){
//				$result =$generator->current();
//				if($result===false) break;
//				$this->stack[]=$generator;
//			}elseif($generator===false) exit;
//
//		}
//	}
//}

/*
//yield 版本
$stack=[];
$result =null;
foreach($app->router->next() as $call =>$args){
	if(is_callable($call)){
		$generator=call_user_func_array($call, [$result]+$args);
	} elseif($call[1] instanceof \Closure){
		$generator=call_user_func_array($call[1]->bindTo($call[0] ?? $app), [$result]+$args);
	}
	if($generator instanceof \Generator){
		if($generator->valid()){
			$result=$generator->current();
			$stack[]=$generator;
		} else $result =$generator->key();
	} else $result =$generator;
}
while($generator=array_pop($stack)){
	while($generator->valid()){
		$generator->send($result);
		$result =$generator->current();
	}
}
var_dump('done.');
*/


$app->run();
var_dump('done.');


/*
function gen(){
	foreach([1, 2, 3] as $id){
		yield $id;
	}
}

$g =gen();

var_dump($g->current());
$g->next();
var_dump($g->current());
$g->next();
var_dump($g->current());
$g->next();
var_dump($g->current());*/
