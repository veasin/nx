<?php
namespace nx\parts;

use JsonException;
use nx\parts\path;

/**
 * @property-read $uuid
 * @property-read \nx\helpers\buffer $buffer
 */
trait runtime{
	protected function nx_parts_runtime():?\Generator{
		$this->buffer['runtime']=[
			'start'=>$_SERVER['REQUEST_TIME'],
			'start-micro'=>microtime(true),
			'list'=>[],
		];
		$method =$_SERVER['REQUEST_METHOD'] ?? 'CLI';
		$uri =$_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME'];
		//$date=date('Y-m-d H:i:s');
		$this->runtime("$method:$uri");
		yield;
		$this->runtime("done.");
		$this->log->log('runtime',"runtime: \n".implode("\n", $this->buffer['runtime']['list'])."\n");
	}
	/**
	 * @param         $var
	 * @param ?string $template
	 */
	public function runtime($var, ?string $template=null):void{
		if(!is_string($var)){
			try{
				$var=json_encode($var, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
			}catch(JsonException){
				$var="Error Json Format.";
			}
		}
		$step=sprintf("%05.2f", (microtime(true) - $this->buffer['runtime']['start-micro']) * 1000);
		$this->buffer['runtime']['list'][]="[$step] $var";
	}
}
