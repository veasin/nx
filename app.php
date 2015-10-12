<?php
namespace nx;

use nx\log\header;

class app{
	protected $buffer =[];
	//protected $response =[];
	/**
	 * @var \nx\request
	 */
	public $request =null;

	public $path ='';

	public function __construct(){
		//初始化use trait
		$uses =class_uses($this);
		foreach($uses as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}

		if($this->path =='') $this->path =dirname($_SERVER['SCRIPT_FILENAME']);

		$this->request =new request();
		//$this->response['app'] =get_class($this);
		header(__NAMESPACE__.':vea 2005-2016');
	}

	public function __destruct(){
		//header_remove('X-Powered-By');
		echo $this->response;
	}

	public function __get($offset){
		switch($offset) {
			case 'response':
				$this->$offset =new o2();
				break;
		}
		return $this->$offset;
	}

	public function __call($name, $args){
		switch($name){
			case 'log':
				call_user_func_array('var_dump', $args);
				break;
			case 'router':
				return $this->control(404);
				break;
			case 'control':
				//echo 'by default control:';
				//$this->log($args[0]);
				header('HTTP/1.0 404 Not Found');
				break;
			case 'config':
				return isset($this->config[$args[0]])
					?$this->config[$args[0]]
					:(isset($args[1])
						?$args[1]
						:null);
			default:
				;
		}
	}

	static public function factory(){
		return new static();
	}

	public function run($route=null){
		if(func_num_args() ==0) $this->router();
		else $this->control($route);
	}
}