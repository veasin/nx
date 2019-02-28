<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/02/25 025
 * Time: 11:22
 */
namespace nx\output;

trait rest{
	protected function nx_output_rest(){
		$this->out->setRender(function(\nx\output $out){
			$status =$out->buffer['status'] ?? 200;
			$this->log( 'rest response status : '.$status);
			header($_SERVER["SERVER_PROTOCOL"].' '.$status);//HTTP/1.1
			header_remove('X-Powered-By');

			$headers =$out->buffer['header'] ?? [];
			$headers['nx']='vea 2005-2019';
			$headers['Status']=$status;
			foreach($headers as $header=>$value){
				if(is_array($value)){
					foreach($value as $v){
						header($header.': '.$v);
					}
				}else header($header.': '.$value);
			}
			$r =$out();
			if(!is_null($r)) echo json_encode($r, JSON_UNESCAPED_UNICODE);
		});
	}
}