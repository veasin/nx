<?php
namespace nx\cache;

/**
 * ['default'=>
 *   'server'=>'mongodb://localhost:27017',
 *   'options'=>[
 *     'connect'=>true,
 *   ],
 *   'driver'=>[],
 * ]
 *
 *
 * Class mongodb
 * @package nx\cache
 */
trait mongodb{
	protected function nx_cache_mongodb(){
		$it=is_a($this, 'nx\mvc\model') ?$this->app :$this;
		$this->buffer['cache/mongodb']=['config'=>isset($it->setup['cache/mongodb']) ?$it->setup['cache/mongodb'] :[], 'handle'=>[],];
	}
	public function cache($name='default'){
		$cache=&$this->buffer['cache/mongodb']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$this->buffer['cache/mongodb']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name]=empty($config)
				?new \MongoClient()
				:new \MongoClient($config['server'], isset($config['options']) ?$config['options'] :[], isset($config['driver']) ?$config['driver'] :[]);
		}
		return $cache[$name];
	}
}
