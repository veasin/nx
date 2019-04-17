<?php
namespace nx;

/**
 * Class autoload
 * @package nx
 * @deprecated 2019-04-17
 */
class autoload{
	protected $map = [];

	public function __construct($map = []){
		$this->map = $map;
		$this->map[__NAMESPACE__] = [__DIR__];
		spl_autoload_register([$this, 'autoload_map']);
	}

	public function autoload($class){
		if(strncmp(__NAMESPACE__ . '\\', $class, 3) !== 0) return;
		$file = __DIR__ . DIRECTORY_SEPARATOR . substr($class, 3) . '.php';
		require $file;
	}

	protected function autoload_map($class){
		if(isset($this->map[$class])){
			require $this->map[$class];
			return true;
		}
		$class = trim($class, '\\');
		if(!strpos($class, '\\') === false) list($prefix, $relative_class) = explode('\\', $class, 2);else $prefix = $relative_class = $class;
		if(isset($this->map[$prefix])){
			if(!is_array($this->map[$prefix])){
				require $this->map[$prefix];
				return true;
				/*
				if (is_file($this->map[$prefix])){
					require $this->map[$prefix];
					return $this->map[$prefix];
				} else return false;*/
			}else
				foreach($this->map[$prefix] as $base_dir){
					$file = $base_dir . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $relative_class) . '.php';
					if(is_file($file)){
						require $file;
						//return $file;
					}else continue;
				}
		}
		return false;
	}

	/**
	 * factory
	 * @param array $map
	 * @return static
	 */
	static public function register($map = []){
		return new static($map);
	}
}

