<?php

namespace nx;
use nx\parts\input;
use nx\parts\o2;

class app implements \ArrayAccess, \Countable{
	use o2, parts\control\main, input;

	/**
	 * @var static|null 静态实例;
	 */
	public static ?app $instance = null;
	public function __construct(array $setup=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[veasin/nx]), need AGREE_LICENSE !');
		$this->data=[...$this->data??[], ...$setup];
		static::$instance = $this;
		//todo 加载setup，需要调整容器可加载容器
		if(!isset($this['app:uuid'])) $this['app:uuid'] = bin2hex(random_bytes(3));
		$this['app:traits'] = array_reverse(array_filter(array_map(function($trait){
					if(method_exists($this, $_method = str_replace('\\', '_', $trait))){
						$r = $this->{$_method}();
						if($r instanceof \Generator) $r->current();
					}
					return $r ?? null;
				}, class_uses($this) + class_uses(__CLASS__))
			)
		);
	}
	public function __destruct(){
		foreach($this['app:traits'] as $trait) $trait->next();
	}
	public function __get($name){
		return $this[$name];
	}
	/**
	 * @throws \Throwable
	 */
	public function __call(string $name, array $arguments): mixed{
		if(property_exists($this, $name) && method_exists($this->$name, '__invoke')) return ($this->$name)(...$arguments);
		if(isset($this[$name]) && is_callable($this[$name])) return call_user_func($this[$name], ...$arguments);
		switch($name){
			case 'throw':
				if($arguments[0] instanceof \Throwable) throw $arguments[0];
				$exp = $arguments[2] ?? '\Exception';
				throw new $exp($arguments[1] ?? '', $arguments[0]);
			case 'runtime':
			case 'log':
				return null;
		}
		throw new \BadMethodCallException(sprintf("Call to undefined method %s->%s()", static::class, $name));
	}
	/**
	 * 执行应用
	 *
	 * @param array ...$route
	 * @return mixed
	 */
	public function run(...$route): mixed{
		return $this->control(...$route);
	}
}