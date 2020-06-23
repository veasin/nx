<?php
namespace nx\helpers\config;

class setup implements \ArrayAccess{
	protected $data=[];
	protected $env='default';
	protected $split=".";
	public function __construct($data=[]){
		$this->data=$data;
	}
	/**
	 * 读取或设定当前环境
	 * @param string|null $env
	 * @return string|null
	 */
	public function env(string $env=null):?string{
		if(null === $env){
			return $this->env;
		}else{
			$temp=$this->data[$this->env];
			$this->env=$env;
			$this->data[$this->env]=$temp;//是否要考虑合并情况？需要做深层数据合并
		}
	}
	/**
	 * 获取指定配置的选项
	 * @param string      $key          配置名
	 * @param null        $defaultValue 如不存在的默认值
	 * @param string|null $env          获取环境
	 * @return mixed|null
	 */
	public function get(string $key, $defaultValue=null, string $env=null){
		$data=$this->data[$env ?? $this->env] ?? $this->data[$this->env];
		return self::_get($data, explode($this->split, $key), $defaultValue);
	}
	/**
	 * 查看配置项是否有配置
	 * @param string $key 配置项
	 * @return bool
	 */
	public function has(string $key):bool{
		return null !== $this->get($key);
	}
	/**
	 * 设定当前或指定环境的配置项值
	 * @param string $key   配置名
	 * @param        $value 设置值
	 * @param null   $env   环境
	 * @return bool
	 */
	private function setWithEnv(string $key, $value, $env=null):bool{
		$env=$env ?? $this->env;
		if(key_exists($env, $this->data)) $this->data[$env]=[];
		return self::_set($this->data[$env], explode($this->split, $key), $value);
	}
	/**
	 * 设置指定配置中的key和对应值，可以一次设置多个环境的
	 * @param string $key   配置名
	 * @param        $value 设置值
	 * @param array  $ex    设置其他环境的值
	 * @return bool
	 */
	public function set(string $key, $value, $ex=[]):bool{
		$ok=$this->setWithEnv($key, $value);
		foreach($ex as $env=>$value){
			$ok=$this->setWithEnv($key, $value, $env);
		}
		return $ok;
	}
	static function _set(&$data, array $keys, $value):bool{
		$count=count($keys);
		if(0 === $count) return false;
		$key=array_shift($keys);
		if('' === $key) return false;
		if(!is_array($data)) $data=[];//允许覆盖原始值. 假定不是NULL或数组的话
		if($count > 1){
			return self::_set($data[$key], $keys, $value);
		}else $data[$key]=$value;
		return true;
	}
	static function _get(&$data, array $keys, $default=null){
		$count=count($keys);
		if(0 === $count) return $default;
		$key=array_shift($keys);
		if('' === $key) return $default;
		if($count > 1){
			return self::_get($data[$key], $keys, $default);
		}else return $data[$key] ?? $default;
	}
	public function offsetSet($offset, $value){
		$this->set($offset, $value);
	}
	public function offsetGet($offset){
		return $this->get($offset);
	}
	public function offsetExists($offset){
		return $this->has($offset);
	}
	public function offsetUnset($offset){
		$this->set($offset, null);
	}
}
