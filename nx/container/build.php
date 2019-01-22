<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/9/6 006
 * Time: 15:38
 */
declare(strict_types=1);
namespace nx\container;

class build implements \Psr\Container\ContainerInterface{
	private $list =[];
	public function __construct($setup=[]){
		$this->list =$setup;
	}
	/**
	 * 生成对象
	 * @param string|object|callable|array $namespace 对象名称
	 * @param array                  $setup     配置参数
	 * @return mixed|null
	 */
	private function _make($namespace, $setup=[]){
		$container=null;
		switch(gettype($namespace)){
			case 'function':
				$container=call_user_func_array($namespace, $setup);
				break;
			case 'string':
				$container=new $namespace(...$setup);
				break;
			case 'object':
				if($namespace instanceof \Closure) $container=call_user_func_array($namespace, $setup);
				else $container =$namespace;
				break;
			case 'array':
				$n =array_shift($namespace);
				$container =$this->_make($n, array_merge($namespace, $setup));
				break;
		}
		return $container;
	}
	/**
	 * 在容器中查找并返回实体标识符对应的对象。
	 *
	 * @param string $id 查找的实体标识符字符串。
	 *
	 * @throws \Psr\Container\NotFoundExceptionInterface  容器中没有实体标识符对应对象时抛出的异常。
	 * @throws \Psr\Container\ContainerExceptionInterface 查找对象过程中发生了其他错误时抛出的异常。
	 *
	 * @return mixed 查找到的对象。
	 */
	public function get($id){
		$n =$this->list[$id] ?? null;
		if(null !==$n) $n =$this->_make($n);
		return $n;
	}
	/**
	 * 如果容器内有标识符对应的内容时，返回 true 。
	 * 否则，返回 false。
	 *
	 * 调用 `has($id)` 方法返回 true，并不意味调用  `get($id)` 不会抛出异常。
	 * 而只意味着 `get($id)` 方法不会抛出 `NotFoundExceptionInterface` 实现类的异常。
	 *
	 * @param string $id 查找的实体标识符字符串。
	 *
	 * @return bool
	 */
	public function has($id){
		return array_key_exists($id, $this->list);
	}
	public function set($id, $value){
		$this->list[$id] =$value;
	}
}