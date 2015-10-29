<?php
namespace nx;

class request extends o2{
	public function __construct($data=[]){
		$this['params'] =$data;
		//var_dump($_SERVER, $_REQUEST, $_GET, $_POST, $_COOKIE, $_ENV);

		$this['method'] =strtolower($_SERVER['REQUEST_METHOD']);
		$this['get'] =$_GET;
		$this['post'] =$_POST;
	}

	public function method(){
		//if(isset($this['method'])) return $this['method'];
		//$this['method'] = strtolower($_SERVER['REQUEST_METHOD']);
		return $this['method'];
	}

	public function headers(){
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
		$name =['Host', 'User-Agent', 'Authorization', 'Accept', 'Accept-Language', 'Accept-Encoding', 'Cookie', 'Connection', 'Content-Type', 'Content-Length', 'Cache-Control', 'Referer', 'X-FireLogger', 'X-FireLoggerAppstats', 'x-insight'];
		foreach($name as $_n) unset($r[$_n]);
		//unset($r['Host'], $r['User-Agent'], $r['Authorization'], $r['Accept'], $r['Accept-Language'], $r['Accept-Encoding'], $r['Cookie'], $r['Connection'], $r['Content-Type'], $r['Content-Length']);
		return $r;
	}

	public function arg($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request arg: '.$name);
		if(!isset($this['args'])) $this['args'] =array_merge($this->get(), $this->post(), $this->input(), $this->params());
		return is_null($name)
			?$this['args']
			:(isset($this['args'][$name])
				?$this->_format_arg($this['args'][$name], $def, $filter, $pattern)
				:$def);
	}

	private function inputAsVar(){
		if(isset($this['inputVar'])) return $this['inputVar'];
		parse_str($this->readInput(), $vars);
		$this['inputVar'] =$vars;
		return $this['inputVar'];
	}

	private function readInput(){
		if(isset($this['input'])) return $this['input'];
		$this['input'] =file_get_contents('php://input');
		return $this['input'];
	}

	public function input($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request input: '.$name);
		$i =$this->inputAsVar();
		return is_null($name)
			?$i
			:(isset($i[$name])
				?$this->_format_arg($i[$name], $def, $filter, $pattern)
				:$def);
	}
	public function params($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request params: '.$name);
		return is_null($name)
			?$this['params']
			:(isset($this['params'][$name])
				?$this->_format_arg($this['params'][$name], $def, $filter, $pattern)
				:$def);
	}
	public function post($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request post: '.$name);
		return is_null($name)
			?$this['post']
			:(isset($this['post'][$name])
				?$this->_format_arg($this['post'][$name], $def, $filter, $pattern)
				:$def);
	}
	public function get($name = null, $def = null, $filter = null, $pattern=''){
		!is_null($name) && \nx\app::$instance->log('request get: '.$name);
		return is_null($name)
			?$this['get']
			:(isset($this['get'][$name])
				?$this->_format_arg($this['get'][$name], $def, $filter, $pattern)
				:$def);
	}

	private function _format_arg($value, $def=null, $filter=null, $pattern=''){
		switch($filter){
			case 'i':
			case 'int':
			case 'integer':
				return (int)$value;
				break;
			case 'f':
			case 'float':
				$_value =trim($value);
				return preg_match('/^[.0-9]+$/', $_value) >0 ?$_value :$def;
				break;
			case 'n':
			case 'num':
				$_value =trim($value);
				return preg_match('/^(\d+)$/', $_value) >0 ?$_value :$def;
				break;
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
				break;
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
				$value =htmlspecialchars($value);
			case null:
				return $value;
				break;
			default:
				if(is_array($filter)){
					if(isset($filter[0])) $value =str_replace($filter, '', $value);
					else foreach($filter as $search =>$replace){
						$value =str_replace($search, $replace, $value);
					}
				}
				return $value;
				break;
		}

	}
}