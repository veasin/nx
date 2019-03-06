<?php
namespace nx\log;

trait file{
	protected function nx_log_file(){
		$setup=$this->setup['log/file'] ?? [];
		$name=date($setup['name'] ?? 'Y-m-d');
		$path=$setup['path'] ?? $this->getPath('./logs/');
		$file=$path.$name.'.log';
		$this->buffer['log/file']=[
			'file'=>$file,
			'line'=>$setup['line'] ?? '[{micro+}] {var}',
			'handle'=>@fopen($file, 'a'),
			'start'=>time(),
			'start-micro'=>microtime(true),
			'method'=>$_SERVER['REQUEST_METHOD'],
			'uri'=>$_SERVER['REQUEST_URI'],
		];
		$this->log('{datetime}:[{method}]{uri}', '{var}');

		yield;
		$this->log("end.\n");
	}
	/**
	 * @param      $var
	 * @param bool $template
	 */
	public function log($var, $template=null){
		$buffer=&$this->buffer['log/file'];
		if(empty($buffer['handle'])) return;
		//$template=$template ?$template :$this->buffer['log/file']['line'];
		//if($onlyvar) return fwrite($this->buffer['log/file']['handle'], $var."\n");
		if(!is_string($var)) $var=json_encode($var, JSON_UNESCAPED_UNICODE);
		$line=str_replace([
			'{var}',
			'{time}',
			'{datetime}',
			'{microtime}',
			'{create}',
			//'{app}',
			'{method}',
			'{uri}',
			'{micro+}',
		], [
			$var,
			date('H:i:s'),
			date('Y-m-d H:i:s'),
			microtime(true),
			time(),
			//__CLASS__,
			$buffer['method'],
			$buffer['uri'],
			sprintf("%06.2fms", (microtime(true) - $buffer['start-micro']) * 1000),
		], "{$this->getUUID()} ".($template ?? $buffer['line']));
		fwrite($buffer['handle'], $line."\n");
	}
}
