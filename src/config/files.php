<?php
namespace nx\config;

/**
 * Trait files
 * @trait   app
 * @package nx\config
 */
trait files{
	/**
	 * @var array 直接缓存结果 config key
	 */
	protected $config=[];
	protected function nx_config_files(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$it->buffer['config/files']=$it->setup['config/files'] ?? [];
		$it->buffer['config/files']['path']=$it->buffer['config/files']['path'] ?? $it->getPath('./config/');
		$it->buffer['config/files']['cache']=$it->setup['config'] ?? [];
	}
	/**
	 * 读取配置内容
	 * @param      $word   "ns.key"
	 * @param null $params 默认值
	 * @return null
	 */
	public function config($word, $params=null){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(array_key_exists($word, $it->config)) return $it->config[$word];
		$_ns=$word;
		$_key=null;
		if(false !== strpos($word, '.')) list($_ns, $_key)=explode('.', $word, 2);
		$buffer=&$it->buffer['config/files']['cache'];
		if(!array_key_exists($_ns, $buffer)){
			$config=[];
			if(is_file($file=$it->buffer['config/files']['path'].$_ns.'.php')){
				$config=@include($file);
			}
			$buffer[$_ns]=$config;
		}
		$it->config[$word]=is_null($_key) ?$buffer[$_ns] :(isset($buffer[$_ns][$_key]) ?$buffer[$_ns][$_key] :$params);
		return $it->config[$word];
	}
}