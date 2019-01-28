<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/3 003
 * Time: 10:58
 */

namespace nx\filter;
/**
 * Class filter
 * @package nx\filter
 * @method filter_key header(string $key=null) 返回指定 header 或全部
 * @method filter_key body(string $key=null) 返回指定 body 或全部
 * @method filter_key query(string $key=null) 返回指定 query 或全部
 * @method filter_key uri(string $key=null) 返回指定 uri 或全部
 * @method filter_key cookie(string $key=null) 返回指定 cookie 或全部
 */
class filter implements \ArrayAccess{
	private $data = [];
	private $setup = [];
	private $rules = [];
	private $key = '';
	private $value = '';
	/**
	 * @var \nx\input
	 */
	private $request = null;
	/**
	 * @var [array] 数据来源
	 */
	private $sources = [];
	public function __construct($setup = []){
		$this->setup = [
			'request'=>$setup['request'] ?? null,
			'from'=>$setup['from'] ?? 'post',
		];
		$this->request =$setup['request'] ?? \nx\app::$instance->in ?? [];
	}
	/**
	 * 添加数据来源
	 * @param string            $name 来源名称
	 * @param null|array|object $from 来源对象或数组 默认request
	 * @return $this
	 */
	public function addSource($name = 'default', $from = null){
		$this->sources[$name] = $from ?? $this->setup['from'];
		return $this;
	}
	/**
	 * 获取数据来源
	 * @param string|array|object $name 来源名称或直接为来源数组或对象
	 * @return array|object
	 */
	private function getSource($name = 'default'){
		if(!is_string($name)) return $name;
		if(isset($this->sources[$name]) || array_key_exists($name, $this->sources)) return $this->sources[$name];
		if($name =='uri') $name ='params';
		$data =&$this->request[$name];
		return $this->sources[$name] = &$data ?? null;
	}
	/**
	 * 添加自定义规则
	 * @param string   $key      规则名
	 * @param callable $callable 规则回调
	 * @return $this
	 */
	public function addRule(string $key, callable $callable){
		$this->rules[$key] = $callable;
		return $this;
	}
	public function get($key, ...$rules){
		$value = $this->data[$key] ?? null;
		$this->key = $key;
		foreach($rules as $rule){
			$value = $this->_check($value, $rule);
		}
		return $value;
	}
	//ArrayAccess
	public function offsetSet($offset, $value){ }   //$this['xx'] ='xx'
	/**
	 * @param mixed $offset
	 * @return \nx\filter\filter_key
	 */
	public function offsetGet($offset){ return $this->key($offset); }           //=$this['zz']
	public function offsetExists($offset){ }       //isset($this['xx']
	public function offsetUnset($offset){ }                //unset($this['xx']
	/**
	 * 返回key对应的验证对象
	 * @param string              $key 键名
	 * @param null|string|array|object $from 来源名
	 * @return filter_key
	 */
	public function key(string $key, $from = null){
		$this->key = $key;
		$data =$this->getSource($from ?? $this->setup['from']);
		$this->value = $data[$this->key] ?? null;
		//return function(...$rules) use($value){
		//	return $this->_check($value, ...$rules);
		//};
		return new class($this->value, $this) implements filter_key{
			private $value;
			private $filter;
			public function __construct($value, $filter){
				$this->value = $value;
				$this->filter = $filter;
			}
			/**
			 * @param mixed ...$args
			 * @return mixed
			 */
			public function __invoke(...$args){//php7
				return $this->filter->check($this->value, ...$args);
			}
			public function filter(...$args){
				return $this->filter->check($this->value, ...$args);
			}
			public function __toString(){
				return $this->filter->check($this->value);
			}
		};
		//return $this;
	}
	public function more($set = []){

	}
	public function __call($from, $args){
		$key =array_shift($args);
		return $this->key($key, $from, ...$args);
	}
	public function check($value, ...$rules){
		foreach($rules as $rule){
			$value = $this->_check($value, $rule);
		}
		return $value;
	}
	private function throw($rule, $code){
		$e = new \nx\exception($rule, $code);
		$e->error = $rule;
		throw $e;
	}
	private function _check($value = null, $args){
		if(is_string($args)) $args =[$args];
		$code = $args['throw'] ?? $this->setup['throw'] ?? 0;
		$rule = array_shift($args);
		switch($rule){
			case '!empty':
			case '!null':
				if(is_null($value)) $this->throw($rule, $code);
				break;
		}
		\nx\app::$instance->logger->debug('filter : [{key} = {value}] {rule} {arg0}', ['key'=>$this->key,'rule'=>$rule, 'value'=>$value ?? 'null', 'arg0'=>$args[0] ?? '']);
		if(is_null($value)) return null;
		switch($rule){
			case 'i':
			case 'int':
			case 'integer':
				$value = (int)$value;
				break;
			case 'f':
			case 'float':
				$value = trim($value);
				if(!preg_match('/^[.0-9]+$/', $value)) $this->throw($rule, $code);
				break;
			case 'n':
			case 'num':
				$value = trim($value);
				if(!preg_match('/^(\d+)$/', $value)) $this->throw($rule, $code);
				break;
			case 'h':
			case 'hex':
				$value = hexdec($value);
				if(is_null($value)) $this->throw($rule, $code);
				break;
			case 'a':
			case 'arr':
			case 'array':
				if(is_string($value)){
					$value = (false !== strpos($value, ',')) ?explode(',', $value) :[$value];
				}
				if(!is_array($value)) $this->throw($rule, $code);
				//$r=[];
				//foreach($value as $_k=>$_v){
				//	$_r=$this->check(...$_v);
				//	if(!is_null($_r)) $r[$_k]=$this->check(...$_v);
				//}
				//return $r;
				break;
			case 'pcre':
			case 'preg':
				$value = trim($value);
				if(!preg_match($args[0], $value)) $this->throw($rule, $code);
				break;
			case 'base64':
				$value = base64_decode($value, empty($args) ?null :$args[0]);
				if(is_null($value)) $this->throw($rule, $code);
				break;
			case 'j':
			case 'json':
				$value = json_decode($value, ...(empty($args) ?[true] :$args));
				if(is_null($value)) $this->throw($rule, $code);
				break;
			case 'len':
			case 'length':
				$len = @strlen($value) ?? 0;
				switch($args[1]){
					case '<':
						$r = $len < $args[0];
						break;
					case '<=':
						$r = $len <= $args[0];
						break;
					case '>':
						$r = $len > $args[0];
						break;
					case '>=':
						$r = $len >= $args[0];
						break;
					default:
						$r = $len == $args[0];
						break;
				}
				if($r) $this->throw($rule, $code);
				break;
			case '=':
			case 'equal':
				if($value !== $args[1] ?? null) $this->throw($rule, $code);
				break;
			case '>':
				if($value <= $args[0] ?? 0) $this->throw($rule, $code);
				break;
			case '<':
				if($value >= $args[0] ?? 0) $this->throw($rule, $code);
				break;
			case 'chinese':
				if(preg_match('/^[\x{3400}-\x{4db5}|\x{4e00}-\x{9fa5}|\x{f900}-\x{fa2c}]+$/iu', $value) === 0) $this->throw($rule, $code);
				break;
			case 'qq':
				if(preg_match('/^(\d{5,11})$/', $value) === 0) $this->throw($rule, $code);
				break;
			case 'mail':
			case 'email':
				if(preg_match('/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i', $value) === 0) $this->throw($rule, $code);
				break;
			case 'phone':
				if(preg_match('/^0\d{2,3}[-]?\d{7,8}$/', $value) === 0) $this->throw($rule, $code);
				break;
			case 'mobile':
				if(preg_match('/^[(\d+)|0]?([13|14|15|17|18]\d{9})$/', $value) === 0) $this->throw($rule, $code);
				break;
			case 'id_card':
				if(preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i', $value) === 0) $this->throw($rule, $code);
				break;
			case 'url':
				if(preg_match('/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/', $value) === 0) $this->throw($rule, $code);
				break;
			case 'ip':
				if(preg_match('/^(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9])\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|\d)$/', $value) === 0) $this->throw($rule, $code);
				break;
			case 'date':
				$value = strtotime($value);
				if(false === $value) $this->throw($rule, $code);
				break;
			default:
				if(array_key_exists($rule, $this->rules)){
					$throw = call_user_func($this->rules[$rule], $value, ...$args);
					if(false === $throw) $this->throw($rule, $code);
				}
				break;
		}
		return $value;
	}
}

