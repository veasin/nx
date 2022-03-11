<?php
namespace nx\parts\config;

use nx\parts\path;

/**
 * Trait files
 * @trait      app
 * @package    nx\config
 * @deprecated 2020-06-23 Vea 使用config对象替换
 * @property-read $app
 */
trait files{
	use path;

	protected function nx_parts_config_files(){
		$it=$this instanceof \nx\app ?$this :$this->app;
		$it->buffer['config/files']=$it->setup['config/files'] ?? [];
		$it->buffer['config/files']['path']=$it->buffer['config/files']['path'] ?? $it->getPath('./config/');
		$it->buffer['config/files']['cache']=$it->setup['config'] ?? [];
		$it->buffer['config/files']['config']=[];//直接缓存结果 config key
	}
	/**
	 * 读取配置内容
	 * @param      $word   "ns.key"
	 * @param null $params 默认值
	 * @return null
	 */
	public function config($word, $params=null):mixed{
		$it=$this instanceof \nx\app ?$this :$this->app;
		if(array_key_exists($word, $it->buffer['config/files']['config'])) return $it->buffer['config/files']['config'][$word];
		$_ns=$word;
		$_key=null;
		if(str_contains($word, '.')) [$_ns, $_key]=explode('.', $word, 2);
		$buffer=&$it->buffer['config/files']['cache'];
		if(!array_key_exists($_ns, $buffer)){
			$config=[];
			if(is_file($file=$it->buffer['config/files']['path'].$_ns.'.php')){
				$config=@include($file);
			}
			$buffer[$_ns]=$config;
		}
		$it->buffer['config/files']['config'][$word]=is_null($_key) ?$buffer[$_ns] :($buffer[$_ns][$_key] ?? $params);
		return $it->buffer['config/files']['config'][$word];
	}
}