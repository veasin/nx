<?php
namespace nx\mvc;

class controller{
	const doExt = 'on';
	const doBefore = 'before';
	const doAfter = 'after';
	/**
	 *
	 * @var \nx\app
	 */
	public $app;
	/**
	 *
	 * @var router
	 */
	public $route = [];
	/**
	 * @var view
	 */
	public $data = null;
	public $_status = [1 => 'error',];
	public function __construct($route, $app){
		$this->app = $app;

		//初始化use trait
		$uses =class_uses($this);
		foreach($uses as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}

		$this->data =$this->response;
		$this->route = $route;

		$this->exec(self::doBefore);
		$this->exec($this->route[1], true);
		$this->exec(self::doAfter);

		$this->app->response =$this->response;
	}
	public function __get($name){
		switch($name){
			case 'response':
				$this->$name =$this->app->response;
				return $this->$name;
				break;
		}
	}
	public function __call($name, $args){
		switch($name){
			case 'view':
				$data =$args[1];
				$data['_file_'] =$args[0];
				return $data;
				break;
			case 'nofound':
				header('HTTP/1.0 404 Not Found');
				break;
		}
	}
	public function exec($name, $hook = false){
		if($hook){
			$found =false;
			foreach([static::doBefore, $this->method(), static::doExt, static::doAfter] as $_prex){
				if(method_exists($this, $_fun =$_prex.$name)){
					$found =true;
					$r =$this->$_fun();
					if($r ===false) break;
				}
			}
			if($found ===false) $this->nofound($name);
		}else if(method_exists($this, $name)) $this->$name();
	}
	/**
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function arg($name = null, $def = null, $filter = null, $pattern=''){
		return $this->app->request->arg($name, $def, $filter, $pattern);
	}
	/**
	 * @param null $method is method
	 * @return bool || method
	 */
	public function method($method = null){
		return $this->app->request->method($method);
	}
	public function config($name, $def=null){
		return $this->app->config($name, $def);
	}
	/**
	 * fn(code, msg), fn(code, data), fn(data), fn(code)
	 * @param int  $code [data=>,err=>]
	 * @param null $data || msg
	 * @return bool
	 */
	public function status($code = 0, $data = null){
		$_data =[];
		if(!is_numeric($code)){
			if(isset($code['data']) || isset($code['err'])) $_data =$code;
			else $_data =['err'=>0, 'data'=>$code];
		}else{
			$_data['err'] = $code;
			if(isset($this->_status[$code])) $_data['msg'] = $this->app->i18n($this->_status[$code]);
			if($code ==0){
				if(func_num_args() >1) $_data['data'] =$data;
				elseif(is_null($data)) $_data['data'] =$this->data->get();
			}
			elseif(is_string($data)) $_data['msg'] = $data;
		}
		$this->data->set($_data);
		return false;


		unset($this->data['_file_']);
		if(!is_numeric($code)){
			if(isset($code['data']) || isset($code['err'])) $this->data->merge($code);else{
				$this->data['err'] = 0;
				$this->data['data'] = $code;
			}
		}else{
			$this->data['err'] = $code;
			if(isset($this->_status[$code])) $this->data['msg'] = $this->app->i18n($this->_status[$code]);
			if($code ==0 && func_num_args() > 1) $this->data['data'] = $data;
			elseif(is_string($data)) $this->data['msg'] = $data;
		}
		return false;
	}
}

