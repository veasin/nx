<?php
namespace nx\parts\log;

trait file{
	protected function nx_parts_log_file(){
		$setup=$this->setup['log/file'] ?? [];
		$this->buffer['log/file']=[
			'line'=>$setup['line'] ?? '[{micro+}] {var}',
			'start'=>time(),
			'start-micro'=>microtime(true),
			'method'=>$_SERVER['REQUEST_METHOD'],
			'uri'=>$_SERVER['REQUEST_URI'],
			'list'=>[],
		];
		$date =date('Y-m-d H:i:s');
		$this->log("{$date} {$_SERVER['REQUEST_METHOD']}:{$_SERVER['REQUEST_URI']}");

		yield;

		$this->log("end.\n");

		$path=$setup['path'] ?? $this->getPath('./logs/');
		$name=date($setup['name'] ?? 'Y-m-d');
		$handle =@fopen($path.$name.'.log', 'a');
		if($handle){
			fwrite($handle, implode("\n", $this->buffer['log/file']['list'])."\n");
			fclose($handle);
		}
	}
	/**
	 * @param      $var
	 * @param bool $template
	 */
	public function log($var, $template=null){
		if(!is_string($var)) $var=json_encode($var, JSON_UNESCAPED_UNICODE);
		$step =sprintf("%06.2fms", (microtime(true) - $this->buffer['log/file']['start-micro']) * 1000);
		$this->buffer['log/file']['list'][] ="{$this->getUUID()} [{$step}] {$var}";
	}
}
