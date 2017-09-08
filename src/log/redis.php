<?php
namespace nx\log;

trait redis{
	public $redis=null;
	public $prefix='';
	protected function nx_log_redis(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($it->buffer['log/redis'])) $it->buffer['log/redis']=['config'=>isset($it->setup['log/redis']) ?$it->setup['log/redis'] :[], 'handle'=>[],];
		$this->redis=$this->connect('default');
		$this->prefix=$it->buffer['log/redis']['config']['prefix']??'default';
		$this->redis->sAdd('trait_redis_log_namespace', $this->prefix);
		$setup =$it->buffer['log/redis']['config'];
		$line =isset($setup['line']) ?$setup['line'] :'[{micro+}] {var}';
		$this->buffer['log/redis'] =[
			'line' =>$line,
			'prefix'=>$this->prefix,
			'start' =>time(),
			'start-micro' =>microtime(true),
		];
		$this->log('{datetime}:[{method}]{uri}', '{var}');
	}
	/**
	 * 连接redis
	 * @param string $name
	 * @return mixed
	 */
	public function connect($name='default'){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$cache=&$it->buffer['log/redis']['handle'];
		if(!isset($cache[$name])){
			$cfg=&$it->buffer['log/redis']['config'];
			$config=false;
			if(isset($cfg[$name])) $config=is_array($cfg[$name]) ?$cfg[$name] :$cfg[$cfg[$name]];
			$cache[$name]=new \redis();
			if(!empty($config)){
				$cache[$name]->connect($config['host'], isset($config['port']) ?$config['port'] :6379, isset($config['timeout']) ?$config['timeout'] :1);
				if(isset($config['auth'])) $cache[$name]->auth($config['auth']);
				if(isset($config['select'])) $cache[$name]->select($config['select']);
			}
		}
		return $cache[$name];
	}
	/**
	 * @param $var
	 * @param bool $template
	 */
	public function log($var, $template =false){
		$template =$template ?$template :$this->buffer['log/redis']['line'];
		//if($onlyvar) return fwrite($this->buffer['log/file']['handle'], $var."\n");

		if(!is_string($var)) $var =json_encode($var, JSON_UNESCAPED_UNICODE);

		$line =str_replace([
			'{var}',
			'{time}',
			'{datetime}',
			'{microtime}',
			'{create}',
			'{app}',
			'{method}',
			'{uri}',
			'{micro+}',
		], [
			$var,
			date('H:i:s'),
			date('Y-m-d H:i:s'),
			microtime(true),
			time(),
			__CLASS__,
			$this->request['method'],
			$this->request['uri'],
			sprintf("%06.2fms", (microtime(true) -$this->buffer['log/redis']['start-micro'])*1000),
		], "{$this->uid} ".$template);
		$this->redis->sAdd($this->prefix, date("Y-m-d"));
		$this->redis->sAdd($this->prefix.'.'.date("Y-m-d"), $this->uid);
		$this->redis->sAdd($this->prefix.'.'.date("Y-m-d").'.'.$this->uid, $line);
	}
}
