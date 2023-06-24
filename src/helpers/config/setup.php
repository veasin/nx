<?php
namespace nx\helpers\config;

class setup implements \ArrayAccess{
	protected array $data=[];
	protected string $split=".";
	public function __construct(array $data=[]){
		$this->data=$data;
	}
	/**
	 * 获取指定配置的选项
	 * @param string      $key          配置名
	 * @param null        $defaultValue 如不存在的默认值
	 * @return mixed|null
	 */
	public function get(string $key, $defaultValue=null):mixed{
		return self::_get($this->data, explode($this->split, $key), $defaultValue);
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
	 * 设置指定配置中的key和对应值，可以一次设置多个环境的
	 * @param string $key   配置名
	 * @param $value 设置值
	 * @return bool
	 */
	public function set(string $key, $value):bool{
		return self::_set($this->data, explode($this->split, $key), $value);
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
	public function offsetSet($offset, $value):void{
		$this->set($offset, $value);
	}
	public function offsetGet($offset):mixed{
		return $this->get($offset);
	}
	public function offsetExists($offset):bool{
		return $this->has($offset);
	}
	public function offsetUnset($offset):void{
		$this->set($offset, null);
	}
}
