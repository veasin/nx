<?php
namespace nx\response;

trait api{
	/**
	 *
	 */
	protected function nx_response_api(){
		$it = is_a($this, 'nx\app') ?$this :$this->app;
		$it->response->contentType('application/json', 'utf-8');
	}
	/**
	 * fn(code, msg), fn(code, data), fn(data), fn(code)
	 * @param int  $code [data=>,err=>]
	 * @param null $data || msg
	 * @return bool
	 */
	public function status($code = 0, $data = null, $die =false){
		$it = is_a($this, 'nx\app') ?$this :$this->app;
		$_data =[];
		if(!is_numeric($code)){
			if(isset($code['data']) || isset($code['err'])) $_data =$code;
			else $_data =['err'=>0, 'data'=>$code];
		}else{
			$_data['err'] = $code;
			if(isset($it->response->status[$code])) $_data['msg'] = $it->i18n($it->response->status[$code]);
			if($code ==0){
				if(func_num_args() >1) $_data['data'] =$data;
				elseif(is_null($data)) $_data['data'] =$it->response->data();
			}
			elseif(is_string($data)) $_data['msg'] = $data;
		}
		($it->response)($_data);
		if($die) die();
		return false;
	}
}