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
	use http;
	protected function nx_parts_output_http():void{}
	protected function nx_parts_output_rest():void{
		$this->out->setRender([$this, 'render_http'], function($r){
			if(!is_null($r)){
				header('Content-Type:application/json; charset=UTF-8');
				try{
					echo json_encode($r, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
				}catch(\JsonException $e){
					echo "Error Format Output.";
				}
			}
		});
	}
}