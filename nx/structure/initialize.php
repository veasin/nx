<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/24 024
 * Time: 11:13
 */
namespace nx\structure;

trait initialize{
	/**
	 * 所有容器
	 * @var array [容器名=>对象]
	 */
	protected $clients=[];
	private $initialized=false;
	private $buffer=[];
	/**
	 * 生成对象
	 * @param string|object|callable $namespace 对象名称
	 * @param array                  $setup     配置参数
	 * @return mixed|null
	 */
	private function _make($namespace, $setup){
		$container=null;
		switch(gettype($namespace)){
			case 'function':
				$container=call_user_func_array($namespace, $setup);
				break;
			case 'string':
				$container=new $namespace(...$setup);
				break;
			case 'object':
				if($namespace instanceof \Closure) $container=call_user_func_array($namespace, $setup);;
				break;
		}
		return $container;
	}
	/**
	 * 初始化方法
	 */
	protected function initialize(){
		if(true === $this->initialized) return;
		foreach($this->buffer as $name=>$setup){
			$model=array_shift($setup);
			$this->clients[$name]=$this->_make($model, $setup);
		}
		$this->initialized=true;
	}
	/**
	 * 注册一个对象到容器内
	 * @param string|null            $name      命名
	 * @param string|object|callable $namespace 类的命名空间|初始函数|callable等
	 * @param mixed                  ...$setup  配置参数
	 * @return $this
	 */
	public function register($name, $namespace, ...$setup){
		if($this->initialized){
			$this->clients[$name]=$this->_make($namespace, $setup);
		}else $this->buffer[$name]=[$namespace, $setup];
		return $this;
	}
}