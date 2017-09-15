<?php
namespace nx\log;

trait redis{
	protected function nx_log_redis(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		if(!isset($it->buffer['log/redis'])) $it->buffer['log/redis']=[
			'config'=>isset($it->setup['log/redis']) ?$it->setup['log/redis'] :[],
			'handle'=>null,
			//'expire'=>0,
		];
		$buffer =&$it->buffer['log/redis'];
		$setup =&$buffer['config'];
		if(is_null($buffer['handle'])){
			if(!array_key_exists('connect', $setup)) die('need ["log"]["connect"].');
			$config =&$setup['connect'];
			$redis =new \Redis();
			$ok =$redis->connect($config['host'], isset($config['port']) ?$config['port'] :6379, isset($config['timeout']) ?$config['timeout'] :1);
			if(false ===$ok) $buffer['handle'] =null;
			if(isset($config['auth'])) $redis->auth($config['auth']);
			if(isset($config['select'])) $redis->select($config['select']);
			$buffer['handle'] =$redis;
		}
		$buffer['line'] =$setup['line'] ?? '[{micro+}] {var}';
		$buffer['start'] =time();
		$buffer['start-micro'] =microtime(true);

		$buffer['expire'] =$setup['expire'] ?? 60*60*24*3;

		$buffer['prefix'] ='log_redis:'.get_class($it);
		$namespace =$setup['namespace'] ?? 'log_redis:namespace';
		$buffer['handle']->sAdd($namespace, $buffer['prefix']);
		$buffer['handle']->expire($namespace, $buffer['expire']);

		$date =date("Y-m-d");
		$dateScore =strtotime($date);
		$buffer['handle']->zAdd($buffer['prefix'], $dateScore, $date);
		$buffer['handle']->zRemRangeByScore($buffer['prefix'], 0, $dateScore-$buffer['expire']);//移除过期日期
		$buffer['handle']->expire($buffer['prefix'], $buffer['expire']);

		$buffer['key'] =date('H:i:s').' ['.$it->request['method'].']'.$it->request['uri'];
		$buffer['handle']->rPush($buffer['prefix'].':'.$date, $buffer['key']);
		$buffer['handle']->expire($buffer['prefix'].':'.$date, $buffer['expire']);
		$buffer['key'] =$buffer['prefix'].':'.$date.':'.$buffer['key'];

		$buffer['handle']->rPush($buffer['key'], $it->request['method'].':'.$it->request['uri']);
		$buffer['handle']->expire($buffer['key'], $buffer['expire']);
	}
	/**
	 * @param $var
	 * @param bool $template
	 */
	public function log($var, $template =false){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		$buffer =&$it->buffer['log/redis'];
		if(is_null($buffer['handle'])) return ;

		$template =$template ?$template :$buffer['line'];

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
			$it->request['method'],
			$it->request['uri'],
			sprintf("%06.2fms", (microtime(true) -$it->buffer['log/redis']['start-micro'])*1000),
		], $template);

		$buffer['handle']->rPush($buffer['key'], $line);
	}
}
