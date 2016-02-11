<?php
namespace nx\response;

trait api{
	/**
	 *
	 */
	protected function nx_response_api(){
		$it = is_a($this, 'nx\app') ?$this :$this->app;
		$it->response = new \nx\response();
	}
	/**
	 * @param string $uri
	 * @param int $code
	 * @return mixed
	 */
	public function redirect($uri = '', $code =302){
		return $this->response->status(302, '', false)->redirect($uri);
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
			if(isset($this->response->status[$code])) $_data['msg'] = $it->i18n($this->response->status[$code]);
			if($code ==0){
				if(func_num_args() >1) $_data['data'] =$data;
				elseif(is_null($data)) $_data['data'] =$this->response->data();
			}
			elseif(is_string($data)) $_data['msg'] = $data;
		}
		$this->response->set($_data);
		if($die) die();
		return false;
	}
}