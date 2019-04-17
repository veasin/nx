<?php
namespace nx\cache;

/**
 * ['default'=>
 *   'host'=>'127.0.0.1',
 *   'port'=>6379,
 *   'timeout'=>0.0,
 * ]
 *
 *
 * Class redis
 * @package nx\cache
 * @deprecated 2019-02-28
 */
trait redis{
	protected function nx_cache_redis(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($it->buffer['cache/redis'])) $it->buffer['cache/redis']=['config'=>isset($it->setup['cache/redis']) ?$it->setup['cache/redis'] :[], 'handle'=>[],];
	}
	public function cache($name='default'){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$cache=&$it->buffer['cache/redis']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$it->buffer['cache/redis']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name]=new \Redis();
			if(!empty($config)){
				$cache[$name]->connect($config['host'], isset($config['port']) ?$config['port'] :6379, isset($config['timeout']) ?$config['timeout'] :1);
				if(isset($config['auth'])) $cache[$name]->auth($config['auth']);
				if(isset($config['select'])) $cache[$name]->select($config['select']);
			}
		}
		return $cache[$name];
	}
}
