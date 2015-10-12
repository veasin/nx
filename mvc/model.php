<?php
namespace nx\mvc;

class model{
	/**
	 *
	 * @var \nx\app
	 */
	public $app = null;
	public $options = [];
	private static $instance = [];
	public $_sql =[];
	public function __construct($set = []){
		$this->options = $set;
		$this->app = \nx\app::$instance;

		//init use trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}

		if(method_exists($this, '_init')) $this->_init();
	}

	/**
	 * @param string $name
	 * @return \PDO
	 */
	protected function db($name ='default'){
		return $this->app->db($name);
	}

	/**
	 * @param array $set
	 * @return self
	 */
	public static function i($set = []){
		$c = get_called_class();
		if(empty(self::$instance[$c])) self::$instance[$c] = new $c($set);
		return self::$instance[$c];
	}
	/**
	 * @param array $set
	 * @return self
	 */
	public static function instance($set = []){
		return self::i($set);
	}
	/**
	 * @param $table
	 * @param $pid
	 * @return hSQL
	 */
	public function sql($table, $pid){
		if(!isset($this->_sql[$table])) $this->_sql[$table] =\nx\helpers\sql::factory($table, $pid);
		return $this->_sql[$table];
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
}
