<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/9/3 003
 * Time: 14:43
 */
declare(strict_types=1);
namespace nx;

use \nx\structure\initialize;
use \Psr\Container\ContainerInterface;

/**
 * Class container [PSR-11]
 * @package nx\container
 */
class container implements ContainerInterface{
	use initialize;
	/**
	 * 数据缓存
	 * @var array
	 */
	protected $data=[];
	public function __construct($setup=[]){
		/**
		 * 初始化数据设置
		 */
		foreach($setup as $id=>$value){
			$this->set($id, $value);
		}
		$this->buffer=$this->get('container');
	}
	/**
	 * 返回指定的container
	 * $app->container($name)
	 * @param string $name 指定的容器名
	 * @return \Psr\Container\ContainerInterface
	 */
	public function __invoke(string $name){
		$this->initialize();
		return $this->clients[$name];
	}
	/**
	 * 设定容器内容
	 * @param string     $id
	 * @param mixed|null $value
	 * @return $this
	 */
	public function set(string $id, $value){
		$this->data[$id]=$value;
		return $this;
	}
	/**
	 * 从容器中创建对象
 	 * $name=>['default',
	 *   'default'=>[\obj, a,b,c],
	 *   'other'=>[\obj, []],
	 * ]
	 * @param string $name
	 * @param string $default
	 * @return null
	 */
	public function create(string $name, $default='default'){
		$config=$this->get($name) ?? [];
		$setup=$config[$config[0] ?? $default] ?? [];
		$new=array_shift($setup);
		if(null === $new) return null;
		return new $new(...($setup ?? []));
	}
	/**
	 * 在容器中查找并返回实体标识符对应的对象。
	 * @param string $id 查找的实体标识符字符串。
	 * @throws \Psr\Container\NotFoundExceptionInterface  容器中没有实体标识符对应对象时抛出的异常。
	 * @throws \Psr\Container\ContainerExceptionInterface 查找对象过程中发生了其他错误时抛出的异常。
	 * @return mixed 查找到的对象。
	 */
	public function get($id){
		if(array_key_exists($id, $this->data)) return $this->data[$id];
		$this->initialize();
		foreach($this->clients as $name=>$container){
			try{
				return $this->data[$id]=$container->get($id);
			}catch(\Exception $e){
				continue;
			}
		}
		return null;
	}
	/**
	 * 如果容器内有标识符对应的内容时，返回 true 。
	 * 否则，返回 false。
	 * 调用 `has($id)` 方法返回 true，并不意味调用  `get($id)` 不会抛出异常。
	 * 而只意味着 `get($id)` 方法不会抛出 `NotFoundExceptionInterface` 实现类的异常。
	 * @param string $id 查找的实体标识符字符串。
	 * @return bool
	 */
	public function has($id){
		if(in_array($id, $this->data)) return true;
		$this->initialize();
		foreach($this->clients as $name=>$container){
			try{
				$has=$container->has($id);
				if(true === $has) return true;
			}catch(\Exception $e){
				continue;
			}
		}
		return false;
	}
}