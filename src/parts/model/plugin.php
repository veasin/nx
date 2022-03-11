<?php
namespace nx\parts\model;

trait plugin{
	/**
	 * 调用指定类型的插件，根据命名有顺序！
	 * @param string $type 插件支持类型
	 * @param mixed  ...$args
	 * @return bool
	 */
	protected function plugin(string $type, ...$args):bool{
		$result=true;
		$prefix="plugin_{$type}_";
		foreach(get_class_methods($this) as $method){
			if(str_starts_with($method, $prefix)){
				$this->log('plugin: '.$method);
				$result_1=call_user_func_array([$this, $method], $args);
				is_bool($result_1) && $result&=$result_1;
			}
		}
		return $result;
	}
}
