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
 */
trait redis{
	protected function nx_cache_redis(){
		$it=is_a($this, 'nx\mvc\model') ?$this->app :$this;
		$this->buffer['cache/redis']=['config'=>isset($it->setup['cache/redis']) ?$it->setup['cache/redis'] :[], 'handle'=>[],];
	}
	public function cache($name='default'){
		$cache=&$this->buffer['cache/redis']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$this->buffer['cache/redis']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name]=new \redis();
			if(empty($config)) $cache[$name]->connect($config['host'], isset($config['host']) ?$config['host'] :11211, isset($config['timeout']) ?$config['timeout'] :1);
		}
		return $cache[$name];
	}
}
