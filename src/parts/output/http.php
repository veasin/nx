<?php
namespace nx\parts\output;

use nx\helpers\http\status;
use nx\helpers\output;

/**
 * @method log(string $string)
 */
trait http{
	public ?output $out =null;
	public function render_http(\nx\helpers\output $out, callable $callback=null):void{
		$r =$out();
		$status =$out->buffer['status'] ?? ( null !==$r ?200 :404);
		$message =status::message($status);
		$this->runtime( "status: $status");
		header(($_SERVER["SERVER_PROTOCOL"] ?? "HTTP/1.1").' '.$message);//HTTP/1.1
		header_remove('X-Powered-By');

		$headers =$out->buffer['header'] ?? [];
		$headers['NX']='Vea 2005-2023';
		foreach($headers as $header=>$value){
			if(is_int($header)){
				if(is_array($value)){
					foreach($value as $v){
						header($header.': '.$v);
					}
				} else if(is_string($value) || $value instanceof \Stringable){
					header($value);//['Status: 200']
				} else{
					//to do nothing...
				}
			}
			else header($header.': '.$value);
		}
		if(null!==$callback) $callback($r);
		else echo $r;
	}
	protected function nx_parts_output_http():?\Generator{
		$this->out =new output();
		$this->out->setRender([$this, 'render_http']);
		yield;
		$this->out =null;
	}
}