<?php
namespace nx;

/*
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
echo $result;

output:
before 1
before 2
after 2
after 1
{"num":10}


$queue->append(function($result, $a, $b, $c){
	return $a.' 1';
});
$queue->append(function($result, $a, $b, $c){
	return $result.' 2';
});
$queue->append(function($result, $a, $b, $c){
	return $result.' 3';
});
$result =$queue->pipe(0, 1, 2);
echo $result;

output:
0 1 2 3
*/

/**
 * 执行队列
 * Class queue
 * @package nx
 * @deprecated 2019-04-17
 */
class queue {
	private $callables=[];
	/**
	 * @var \nx\o2;
	 */
	protected $var=null;
	public function __construct(){
		$this->var =new o2();
	}
	/**
	 * 添加回调到列表中
	 * @param callable $callable
	 * @return $this
	 */
	public function append(callable $callable){
		if(is_callable($callable)) $this->callables[]=$callable;
		return $this;
	}
	/**
	 * 中间件模式 碰到第一个yield会转移到下一个上，执行完后再返回上层，回调第一个参数为o2用来传递结果
	 * @param array ...$args
	 * @return o2
	 */
	public function middleware(...$args){
		$this->var->clear();
		array_unshift($args, $this->var);
		$stack=[];
		foreach($this->callables as $callable){
			$generator=call_user_func_array($callable, $args);
			if($generator && $generator instanceof \Generator){
				$result =$generator->current();
				if($result===false) break;
				$stack[]=$generator;
			}elseif($generator===false) break;
		}
		while($generator=array_pop($stack)) $generator->next();
		$this->callables =[];
		return $this->var;
	}
	/**
	 * 管道模式 前一个返回结果会当成后一个的参数（第一个参数，其他依次后移）直接传递进去，返回最后的return
	 * @param array ...$args
	 * @return mixed
	 */
	public function pipe(...$args){
		array_unshift($args, null);
		foreach($this->callables as $callable){
			$result=call_user_func_array($callable, $args);
			$args[0] =$result;
		}
		$this->callables =[];
		return $args[0];
	}
	/**
	 * 清空执行队列
	 * @return $this
	 */
	public function clear(){
		$this->callables =[];
		return $this;
	}
}