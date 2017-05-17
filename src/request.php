<?php
namespace nx;

class request extends o2{
	private $is_cli =false;
	public function __construct($data=[]){
		$this->is_cli =PHP_SAPI=='cli';
		$this['params'] =$data;//构建数据
		$this['method'] =$this->is_cli ?'cli' : strtolower($_SERVER['REQUEST_METHOD']);
		$this['get'] =$_GET;
		$this['post'] =$_POST;
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
	 * 返回当前请求头（有清理）
	 * @param bool|false $clear
	 * @return array|false
	 */
	public function headers($clear=false){
		if (!function_exists('getallheaders')){
			function getallheaders(){
				$headers = [];
				foreach ($_SERVER as $name => $value){
					if (substr($name, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
				return $headers;
			}
		}
		$r =getallheaders();
		if($clear){
			$name = ['Host',
				'User-Agent',
				'Authorization',
				'Accept',
				'Accept-Language',
				'Accept-Encoding',
				'Cookie',
				'Connection',
				'Content-Type',
				'Content-Length',
				'Cache-Control',
				'Referer',
				'X-FireLogger',
				'X-FireLoggerAppstats',
				'x-insight'];
			foreach($name as $_n) unset($r[$_n]);
		}
		return $r;
	}
	/**
	 * 读取当前请求中input中的参数，详见arg方法
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function header($name = null, $def = null, $filter = null, $pattern='', $clear=false){
		!is_null($name) && \nx\app::$instance->log('request header: '.$name);
		if(!isset($this['header'])) $this['header'] =$this->headers($clear);
		return is_null($name)
			?$this['header']
			:(isset($this['header'][$name])
				?$this->_format_arg($this['header'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 获取当前请求参数（所有方式）
	 * @param null $name 参数名
	 * @param null $def 如果不存在返回此
	 * @param null $filter 验证格式 i=int=integer存在转换整型,f=float存在转换为浮点,n=num正则验证数字,a=arr=array验证数组,prce=preg正在验证,b=bool=boolean存在转换为布尔,w=word单词去除标点,s=str=string转换为字符串
	 * @param string $pattern 验证格式辅助
	 * @return array|mixed|null|string
	 */
	public function arg($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request arg: '.$name);
		if(!isset($this['args'])) $this['args'] =array_merge($this->get(), $this->post(), $this->input(), $this->params());
		return is_null($name)
			?$this['args']
			:(isset($this['args'][$name])
				?$this->_format_arg($this['args'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 转化input中内容为对应的变量值
	 * @return mixed
	 */
	public function inputAsVar(){
		if(isset($this['inputVar'])) return $this['inputVar'];
		parse_str($this->inputString(), $vars);
		$this['inputVar'] =$vars;
		return $this['inputVar'];
	}
	/**
	 * 转化input中内容为对应的json对象
	 * @return mixed
	 */
	public function inputAsJson(){
		if(isset($this['inputJson'])) return $this['inputJson'];
		$this['inputVar'] =json_decode($this->inputString(), true);
		return $this['inputVar'];
	}
	/**
	 * 读取input，用在扩充put delete等情况
	 * @return mixed|string
	 */
	public function inputString(){
		if(isset($this['input'])) return $this['input'];
		$this['input'] =file_get_contents('php://input');
		return $this['input'];
	}
	/**
	 * 读取当前请求中input中的参数，详见arg方法
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function input($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request input: '.$name);
		$i =$this->inputAsVar();
		return is_null($name)
			?$i
			:(isset($i[$name])
				?$this->_format_arg($i[$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 读取当前请求中params中的参数，详见arg方法
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function params($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request params: '.$name);
		return is_null($name)
			?$this['params']
			:(isset($this['params'][$name])
				?$this->_format_arg($this['params'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 读取当前请求中post中的参数，详见arg方法
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function post($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request post: '.$name);
		return is_null($name)
			?$this['post']
			:(isset($this['post'][$name])
				?$this->_format_arg($this['post'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 读取当前请求中get中的参数，详见arg方法
	 * @param null $name
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|null|string
	 */
	public function get($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request get: '.$name);
		return is_null($name)
			?$this['get']
			:(isset($this['get'][$name])
				?$this->_format_arg($this['get'][$name], $def, $filter, $pattern)
				:$def);
	}
	/**
	 * 返回当前上传的文件，并验证是否可用
	 * @param $arg
	 * @return bool
	 */
	public function file($arg){
		$f =&$_FILES[$arg];
		return (isset($f['name']) && isset($f['type']) && isset($f['size']) && isset($f['tmp_name']) && isset($f['error']) && ($f['error'] == UPLOAD_ERR_OK) && is_file($f['tmp_name']) && is_uploaded_file($f['tmp_name']) && is_readable($f['tmp_name'])) ?$f :false;
	}
	/**
	 * 格式化或过滤参数
	 * @param $value
	 * @param null $def
	 * @param null $filter
	 * @param string $pattern
	 * @return array|mixed|string
	 */
	private function _format_arg($value, $def=null, $filter=null, $pattern=''){
		switch($filter){
			case null:
				return $value;
			case 'i':
			case 'int':
			case 'integer':
				return (int)$value;
			case 'f':
			case 'float':
				$_value =trim($value);
				return preg_match('/^[.0-9]+$/', $_value) >0 ?$_value :$def;
			case 'n':
			case 'num':
				$_value =trim($value);
				return preg_match('/^(\d+)$/', $_value) >0 ?$_value :$def;
			case 'a':
			case 'arr':
			case 'array':
				if(is_string($value)){
					if(strpos($value, ',') !==false) $value =explode(',', $value);
					else $value =[$value];
				}
				if(!is_array($value)) return $def;
				$r =[];
				foreach($value as $_k =>$_v){
					$_r =$this->_format_arg($_v, null, $pattern);
					if(!is_null($_r)) $r[$_k] =$this->_format_arg($_v, null, $pattern);
				}
				return $r;
			case 'pcre':
			case 'preg':
				$_value =trim($value);
				return preg_match($pattern, $_value) >0 ?$_value :$def;
			case 'b':
			case 'bool':
			case 'boolean':
				return (boolean)$value;
			case 'w':
			case 'word':
				if(strpos($value, ';') !==false || strpos($value, ')') !==false || strpos($value, '(') !==false) return $def;
			case 's':
			case 'str':
			case 'string':
				$value =(string)$value;
				return $value;
			case 'base64':
				$v =base64_decode($value, empty($pattern) ?null :true);
				return $v ?$v :$def;
			default:
				if(is_array($filter)){
					if(isset($filter[0])) $value =str_replace($filter, '', $value);
					else foreach($filter as $search =>$replace){
						$value =str_replace($search, $replace, $value);
					}
				}
				return $value;
		}

	}
	/**
	 * 返回请求ip
	 * @return mixed
	 */
	public function ip(){
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
}