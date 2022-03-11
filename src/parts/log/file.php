<?php
namespace nx\parts\log;

use JsonException;
use nx\parts\path;

/**
 * @property-read $uuid
 * @property-read \nx\helpers\buffer $buffer
 */
trait file{
	use path;

	protected function nx_parts_log_file():?\Generator{
		$setup=$this->setup['log/file'] ?? [];
		$method =$_SERVER['REQUEST_METHOD'] ?? 'CLI';
		$uri =$_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'];
		$this->buffer['log/file']=[
			'line'=>$setup['line'] ?? '[{micro+}] {var}',
			'start'=>time(),
			'start-micro'=>microtime(true),
			'method'=>$method,
			'uri'=>$uri,
			'list'=>[],
		];
		$date=date('Y-m-d H:i:s');
		$this->log("$date $method:$uri");
		yield;
		$this->log("end.\n");
		$path=$setup['path'] ?? $this->getPath('./logs/');
		$name=date($setup['name'] ?? 'Y-m-d');
		$handle=@fopen($path.$name.'.log', 'ab');
		if($handle){
			fwrite($handle, implode("\n", $this->buffer['log/file']['list'])."\n");
			fclose($handle);
		}
	}
	/**
	 * @param         $var
	 * @param ?string $template
	 */
	public function log($var, ?string $template=null):void{
		if(!is_string($var)){
			try{
				$var=json_encode($var, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
			}catch(JsonException){
				$var="Error Json Format.";
			}
		}
		$step=sprintf("%06.2fms", (microtime(true) - $this->buffer['log/file']['start-micro']) * 1000);
		$this->buffer['log/file']['list'][]="$this->uuid [$step] $var";
	}
}
