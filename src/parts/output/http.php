<?php
namespace nx\parts\output;

use nx\helpers\network\context\status;

trait http{
	public function render_http(\nx\output $out, callable $callback=null):void{
		$r =$out();
		$status =$out->buffer['status'] ?? ( null !==$r ?200 :404);
		$message =status::$Message[$status] ?? '';
		$this->log( 'status: '.$status.' '.$message);
		header($_SERVER["SERVER_PROTOCOL"].' '.$status.' '.$message);//HTTP/1.1
		header_remove('X-Powered-By');

		$headers =$out->buffer['header'] ?? [];
		$headers['nx']='vea 2005-2022';
		$headers['Status']=$status;
		foreach($headers as $header=>$value){
			if(is_array($value)){
				foreach($value as $v){
					header($header.': '.$v);
				}
			}elseif(is_int($header)) header($value);
			else header($header.': '.$value);
		}
		if(null!==$callback) $callback($r);
		else echo $r;
	}
	protected function nx_parts_output_http():void{
		$this->out->setRender([$this, 'render_http']);
	}
}