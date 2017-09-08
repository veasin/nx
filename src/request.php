<?php
namespace nx;

class request extends o2{
	private $is_cli=false;
	public function __construct($data=[]){
		$this->is_cli=(PHP_SAPI == 'cli');
		if($this->is_cli){
			$argv=$_SERVER['argv'];
			array_shift($argv);
			$this['params']=$argv;
			$this['method']='cli';
			$this['uri']=implode(' ', $_SERVER['argv']);
		}else{
			$this['params']=$data;//构建数据
			$this['method']=strtolower($_SERVER['REQUEST_METHOD']);
			$this['uri']=$_SERVER['REQUEST_URI'];
			$this['get']=$_GET;
			$this['post']=$_POST;
		}
	}
	public function &offsetGet($offset){
		switch($offset){
			case 'body':
				if(!array_key_exists('body', $this->data)){
					$this->data['body']=file_get_contents('php://input');
				}
				break;
			case 'header':
				if(!array_key_exists('header', $this->data)){
					if(!function_exists('getallheaders')){
						$this->data['header']=[];
						foreach($_SERVER as $name=>$value){
							if('HTTP_' === substr($name, 0, 5)) $this->data['header'][str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))]=$value;
						}
					}else{
						$this->data['header']=[];
						$headers=getallheaders();
						foreach($headers as $key=>$value){
							$this->data['header'][strtolower($key)]=$value;
						}
					}
				}
				break;
			case 'input':
				$this->data['input']=[];
				if('p' === $this['method'][0]){//'post', 'put', 'patch'
					if('post' === $this['method'])
						$this->data['input']=$_POST;
					else{
						$this->data['body']=file_get_contents('php://input');
						switch($this->header('content-type')){//触发header更新
							case 'application/x-www-form-urlencoded':
								parse_str($this->data['body'], $vars);
								$this->data['input']=$vars;
								break;
							case 'application/json':
								$this->data['input']=json_decode($this->data['body'], true);
								break;
							case 'application/xml':
								$xml=simplexml_load_string($this->data['body']);
								$this->data['input']=json_decode(json_encode($xml), true);
								break;
							case 'text/plain':
							case 'text/html':
								break;
						}
					}
				}
				break;
		}
		return $this->data[$offset];
	}
	/**
	 * 返回当前请求的method或验证method是否正确
	 * @param bool|false $method 验证是否为此method
	 * @return bool|string
	 */
	public function method($method=false){
		return ($method) ?$this['method'] == strtolower($method) :$this['method'];
	}
	/**
	 * 从文件头中取出
	 * @param null   $name
	 * @param null   $default
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	public function header($name=null, $default=null, $filter=null, $pattern=''){
		return $this->_call('header', strtolower($name), $default, $filter, $pattern);
	}
	/**
	 * 从 $_GET 中取出
	 * @param null   $name
	 * @param null   $default
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	public function get($name=null, $default=null, $filter=null, $pattern=''){
		return $this->_call('get', $name, $default, $filter, $pattern);
	}
	/**
	 * 从 $_POST 中取出
	 * @param null   $name
	 * @param null   $default
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	public function post($name=null, $default=null, $filter=null, $pattern=''){
		return $this->_call('post', $name, $default, $filter, $pattern);
	}
	/**
	 * 从 网址url 中取出
	 * @param null   $name
	 * @param null   $default
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	public function params($name=null, $default=null, $filter=null, $pattern=''){
		return $this->_call('params', $name, $default, $filter, $pattern);
	}
	/**
	 * 从 php://input 中取出
	 * @param null   $name
	 * @param null   $default
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	public function input($name=null, $default=null, $filter=null, $pattern=''){
		return $this->_call('input', $name, $default, $filter, $pattern);
	}
	private function _call($from, $name, ...$arguments){
		!is_null($name) && \nx\app::$instance->log('request '.$from.': '.$name);
		$data =&$this[$from];
		return $this->_filter($data[$name] ?? null, ...$arguments);
	}
	//public function __call($from, ...$arguments){
	//	$name=array_shift($arguments);
	//	switch($from){
	//		case 'header':
	//			$name =strtolower($name);
	//		case 'get':
	//		case 'post':
	//		case 'params':
	//		case 'input':
	//			!is_null($name) && \nx\app::$instance->log('request '.$from.': '.$name);
	//			return $this->_filter($this[$from][$name] ?? null, ...$arguments);
	//			break;
	//	}
	//}
	/**
	 * 返回当前上传的文件，并验证是否可用
	 * @param $arg
	 * @return bool
	 */
	public function file($arg){
		$f=&$_FILES[$arg];
		return (isset($f['name']) && isset($f['type']) && isset($f['size']) && isset($f['tmp_name']) && isset($f['error']) && ($f['error'] == UPLOAD_ERR_OK) && is_file($f['tmp_name']) && is_uploaded_file($f['tmp_name']) && is_readable($f['tmp_name']))
			?$f :false;
	}
	/**
	 * 返回请求ip
	 * @return mixed
	 */
	public function ip(){
		if(!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
	/**
	 * 格式化或过滤参数
	 * @param        $value
	 * @param null   $def
	 * @param null   $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	private function _filter($value, $def=null, $filter=null, $pattern=''){
		switch($filter){
			case null:
				return $value ?? $def;
			case 'i':
			case 'int':
			case 'integer':
				return (int)($value ?? $def);
			case 'f':
			case 'float':
				$_value=trim($value);
				return preg_match('/^[.0-9]+$/', $_value) > 0 ?$_value :$def;
			case 'n':
			case 'num':
				$_value=trim($value);
				return preg_match('/^(\d+)$/', $_value) > 0 ?$_value :$def;
			case 'a':
			case 'arr':
			case 'array':
				if(is_string($value)){
					if(strpos($value, ',') !== false) $value=explode(',', $value);else $value=[$value];
				}
				if(!is_array($value)) return $def;
				$r=[];
				foreach($value as $_k=>$_v){
					$_r=$this->_filter($_v, null, $pattern);
					if(!is_null($_r)) $r[$_k]=$this->_filter($_v, null, $pattern);
				}
				return $r;
			case 'pcre':
			case 'preg':
				$_value=trim($value);
				return preg_match($pattern, $_value) > 0 ?$_value :$def;
			case 'b':
			case 'bool':
			case 'boolean':
				return (boolean)$value;
			case 'w':
			case 'word':
				if(strpos($value, ';') !== false || strpos($value, ')') !== false || strpos($value, '(') !== false) return $def;
			case 's':
			case 'str':
			case 'string':
				$value=(string)$value;
				return $value;
			case 'base64':
				$v=base64_decode($value, empty($pattern) ?null :true);
				return $v ?$v :$def;
			default:
				if(is_array($filter)){
					if(isset($filter[0])) $value=str_replace($filter, '', $value);else foreach($filter as $search=>$replace){
						$value=str_replace($search, $replace, $value);
					}
				}
				return $value;
		}
	}
}