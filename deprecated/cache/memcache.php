<?php
namespace nx\cache;

/**
 * ['default'=>
 *   'host'=>'127.0.0.1',
 *   'port'=>11211,
 *   'timeout'=>1,
 * ]
 *
 *
 * Class memcache
 * @package nx\cache
 * @deprecated 2019-02-28
 */
trait memcache{
	protected function nx_cache_memcache(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($it->buffer['cache/memcache'])){
			$it->buffer['cache/memcache']=['config'=>isset($it->setup['cache/memcache']) ?$it->setup['cache/memcache'] :[], 'handle'=>[],];
		}
	}
	/**
	 * @param string $name
	 * @return \Memcache
	 */
	public function cache($name='default'){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$cache=&$it->buffer['cache/memcache']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$it->buffer['cache/memcache']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name] = new \Memcache();
			if(!empty($config)) $cache[$name]->connect($config['host'], isset($config['port']) ?$config['port'] :11211, isset($config['timeout']) ?$config['timeout'] :1);
		}
		return $cache[$name];
	}
}
