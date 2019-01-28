<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/9/6 006
 * Time: 15:38
 */
declare(strict_types=1);
namespace nx\container;

class config implements \Psr\Container\ContainerInterface{
	protected $path='';
	private $data=[];
	private $cache=[];
	public function __construct($setup=[]){
		$this->path =$setup['path'] ?? \nx\app::$instance->getPath('/config/');
		$this->data =$setup['data'] ?? [];
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
		if(array_key_exists($id, $this->data)) return $this->data[$id];
		$_ns=$id;
		$_key=null;
		if(false !== strpos($id, '.')) list($_ns, $_key)=explode('.', $id, 2);
		if(!array_key_exists($_ns, $this->cache)){
			$config =[];
			if(is_file($file=$this->path.$_ns.'.php')){
				$config=@include($file);
			}
			$this->cache[$_ns]=$config;
		}
		$this->data[$id]=is_null($_key)
			?$this->cache[$_ns]
			:($this->cache[$_ns][$_key] ?? null);
		return $this->data[$id];
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
		return array_key_exists($id, $this->data);
	}
	public function set($id, $value){
		$this[$id] =$value;
	}
}