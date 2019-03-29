<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/09/18 018
 * Time: 09:55
 */
namespace nx\validator;

trait filter{
	/**
	 * 格式化或过滤参数
	 * @param       $value
	 * @param null  $def
	 * @param null  $filter
	 * @param array ...$pattern
	 * @return array|mixed|null|string
	 * @deprecated 2019-03-29
	 */
	public function filter($value, $def=null, $filter=null, ...$pattern){
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
			case 'h':
			case 'hex':
				return !is_null($value) ?hexdec($value) :$def;
			case 'a':
			case 'arr':
			case 'array':
				if(is_string($value)){
					if(strpos($value, ',') !== false) $value=explode(',', $value);else $value=[$value];
				}
				if(!is_array($value)) return $def;
				$r=[];
				foreach($value as $_k=>$_v){
					$_r=$this->filter($_v, null, ...$pattern);
					if(!is_null($_r)) $r[$_k]=$this->filter($_v, null, ...$pattern);
				}
				return $r;
			case 'pcre':
			case 'preg':
				$_value=trim($value);
				return preg_match($pattern[0], $_value) > 0 ?$_value :$def;
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
				$v=base64_decode($value, empty($pattern) ?null :$pattern[0]);
				return $v ?$v :$def;
			case 'j':
			case 'json':
				$v=json_decode($value, ...(empty($pattern) ?[true] : $pattern));
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