<?php
namespace nx\mvc;

class model{
	/**
	 *
	 * @var \nx\app
	 */
	public $app = null;
	public $setup = [];
	static private $instance = [];

	public $buffer =[];
	protected $lastError =[1, '未知错误'];
	public function __construct($setup = []){
		$this->setup = $setup;
		$this->app = \nx\app::$instance;

		//init use trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}
		/**
		 * 如果本身存在init方法，那么就立刻执行
		 */
		if(method_exists($this, 'init')) $this->init();
	}

	/**
	 * 实例化model并缓存
	 * @param array $set
	 * @return static
	 */
	static public function instance($set = []){
		$c = get_called_class();
		if(empty(self::$instance[$c])) self::$instance[$c] = new $c($set);
		return self::$instance[$c];
	}
	/**
	 * 返回最后的错误信息，需要在model中提前指定
	 * @return array
	 */
	public function lastError(){
		return $this->lastError;
	}

	public function __call($name, $args){
		switch($name){
			default:
				return call_user_func_array([$this->app, $name], $args);
		}
	}
}
