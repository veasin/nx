<?php
namespace nx\log;

trait file{

	protected function nx_log_file(){
		$setup =isset($this->setup['log/file']) ?$this->setup['log/file'] :[];
		$name =date(isset($setup['name']) ?$setup['name'] :'Y-m-d');
		$line =isset($setup['line']) ?$setup['line'] :'[{micro+}] {var}';
		$path =isset($setup['path']) ?$setup['path'] :$this->path.'/logs/';
		$file =$path.$name.'.log';
		$this->buffer['log/file'] =[
			'file' =>$file,
			'line' =>$line,
			'handle' =>@fopen($file, 'a'),
			'start' =>time(),
			'start-micro' =>microtime(true),
		];

		$this->log('{datetime}:[{method}]{uri}', '{var}');
	}
	/**
	 * @param $var
	 * @param bool $template
	 */
	public function log($var, $template =false){
		if(empty($this->buffer['log/file']['handle'])) return ;

		$template =$template ?$template :$this->buffer['log/file']['line'];

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
			sprintf("%06.2fms", (microtime(true) -$this->buffer['log/file']['start-micro'])*1000),
		], "{$this->uid} ".$template);

		fwrite($this->buffer['log/file']['handle'], $line."\n");
	}

}
