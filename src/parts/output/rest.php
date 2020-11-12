<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/02/25 025
 * Time: 11:22
 */
namespace nx\parts\output;

use nx\helpers\network\context\status;

trait rest{
	protected function nx_parts_output_rest(){
		$this->out->setRender(function(\nx\output $out){
			$r =$out();
			$status =$out->buffer['status'] ?? ( null !==$r ?200 :404);
			$message =status::$Message[$status] ?? '';
			$this->log( 'status: '.$status.' '.$message);
			header($_SERVER["SERVER_PROTOCOL"].' '.$status.' '.$message);//HTTP/1.1
			header_remove('X-Powered-By');

			$headers =$out->buffer['header'] ?? [];
			$headers['nx']='vea 2005-2020';
			$headers['Status']=$status;
			foreach($headers as $header=>$value){
				if(is_array($value)){
					foreach($value as $v){
						header($header.': '.$v);
					}
				}elseif(is_int($header)) header($value);
				else header($header.': '.$value);
			}
			if(!is_null($r)){
				header('Content-Type:application/json charset=UTF-8');
				echo json_encode($r, JSON_UNESCAPED_UNICODE);
			}
		});
	}
}