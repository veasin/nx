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
	public function __construct($setup = []){
		$this->setup = $setup;
		$this->app = \nx\app::$instance;

		//init use trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}

		if(method_exists($this, '_init')) $this->_init();
	}

	/**
	 * @param array $set
	 * @return static
	 */
	static public function instance($set = []){
		$c = get_called_class();
		if(empty(self::$instance[$c])) self::$instance[$c] = new $c($set);
		return self::$instance[$c];
	}

	/**
	 * [[key, val, oth],[key, val, oth]...]
	 * (key, val)=>[key=>val],
	 * (key, fun)=>[key=>fun(val)]
	 * (key,false)=>[key=>[key, val, oth]],
	 * (null, val)=>[val, val],
	 * (null, fun)=>[fun(val)]
	 * (null, false) =>$array
	 * @param     $array
	 * @param int $key
	 * @param int $value
	 * @return array
	 */
	public function _map($array, $key = 0, $value = 1){
		if(!is_array($array)) return $array;
		$r = [];
		if(is_null($key)){
			if($value ===false) return $array;
			foreach($array as $_key => $_value){
				$r[] =is_callable($value)
						?$value($_value, $_key)
						:$_value[$value];
			}
		}else{
			foreach($array as $_key => $_value){
				$r[$_value[$key]] =($value ===false)
					?$_value
					:(is_callable($value)
						?$value($_value, $_key)
						:$_value[$value]);
			}
		}
		return $r;
	}
	public function __call($name, $args){
		switch($name){
			default:
				return call_user_func_array([$this->app, $name], $args);
		}
	}
}
