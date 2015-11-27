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
 */
trait memcache{
	protected function nx_cache_memcache(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($this->buffer['cache/memcache'])) $this->buffer['cache/memcache']=['config'=>isset($it->setup['cache/memcache']) ?$it->setup['cache/memcache'] :[], 'handle'=>[],];
	}
	public function cache($name='default'){
		$cache=&$this->buffer['cache/memcache']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$this->buffer['cache/memcache']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name]=empty($config)
				?new \memcache()
				:new \memcache($config['host'], isset($config['host']) ?$config['host'] :11211, isset($config['timeout']) ?$config['timeout'] :1);
		}
		return $cache[$name];
	}
}
