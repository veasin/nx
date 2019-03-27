<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/25 025
 * Time: 14:24
 */

namespace nx\validator;

trait filterThrow{
	private function nx_filter_throw($check, $error=null, $throw=null){
		$default =[
			'throw'=>400,
			'error'=>'\Exception',
			'message'=>[
				'unknown'=>'未知规则错误: {rule}',
				'from'=>'无法找到指定来源: {from}[{name}]',
				'default'=>'来源为空: {from}[{name}]',
				'>'=>'{from}[{name}]值不大于{check}',
				'<'=>'{from}[{name}]值不小于{check}',
				'>='=>'{from}[{name}]值小于{check}',
				'<='=>'{from}[{name}]值大于{check}',
				'array'=>'无效的数组值{from}[{name}]',
				'json'=>'无效的json值{from}[{name}]',
				'source'=>'错误的数据来源',
				'length'=>'{from}[{name}]值长度不正确',
				'length='=>'{from}[{name}]值长度不为{check}',
				'length<'=>'{from}[{name}]值长度多于{check}',
				'length>'=>'{from}[{name}]值长度少于{check}',
				'number'=>'{from}[{name}]无效的数字格式',
				'pcre'=>'{from}[{name}]无效的内容，未通过验证',
				'email'=>'{from}[{name}]无效的邮箱格式',
				'url'=>'{from}[{name}]无效的地址格式',
				'china-mobile'=>'{from}[{name}]无效的手机号码格式',
				'china-id'=>'{from}[{name}]无效的身份证号格式',
				'callback'=>'{from}[{name}]无效内容无法通过自定义检测',
				'empty'=>'无效的参数值，值为空',
			]
		];
		$it=is_a($this, 'nx\app') ?$this :(property_exists($this, 'app') ?$this->app :false);
		if(false !==$it){
			$set=array_merge($default, $it->setup['filter/throw'] ?? []);
		} else $set =$default;

		if(!array_key_exists('message', $check)){
			$keys=array_map(function($value){
				return '{'.$value.'}';
			}, array_keys($check));
			$check['message']=str_replace($keys, array_values($check), $set['message'][$check['rule'] ?? 'unknown'] ?? $set['message']['unknown']);
		}
		$error =$error ?? $check['error'] ?? $set['error'];
		$throw =$throw ?? $check['throw'] ?? $set['throw'];
		$exp =new $error($check['message'], $throw);
		$exp->rule =$check;
		throw $exp;
	}
	public function filter($vars=[], $options=[]){
		$data =[];
		$single =false;
		if(is_string($vars)){//单字段模式 filter('id', [], []) =>filter(['id'=>[], [])
			$single =true;
			$vars =[$vars=>$options];
			$options =func_num_args()>2 ?func_get_arg(2) :[];
		}
		foreach($vars as $key=>$rules){
			if(is_callable($rules)) $rules =['callback'=>$rules];//callback 特殊设置
			if(!is_array($rules)) $rules =[$rules];//'int'  =>['int']
			$rules =array_merge($options, $rules);//合并默认设置
			$valids=[];//后置检验规则
			$check=[
				'from'=>'body',
				'name'=>$key,
			];
			$from_set =[];//从规则列表中分离来源设置
			$source =[];//指定来源为 source时，来源值
			$default_set =false; //默认值设置
			foreach($rules as $rule=>$set){//['rule'=>'from', 'value'=>'body', 'key'=>'cid', 'throw'=>400, 'error'=>'\nx\exception\filter\http'],
				if(is_int($rule) && is_numeric($rule)){
					$_set =!is_array($set) ?['rule'=>$set] :$set;// 'int', 'url', '>0' => ['rule'=>'int']
				} else{
					$_set =!is_array($set) ?['value'=>$set] :$set;
					if(!array_key_exists('rule', $_set)) $_set['rule'] =$rule;
				}
				$_org_set =$set;
				$set =array_merge($rules, $_set);
				//前置规则整理（获取指定key的值）
				switch($set['rule']){
					/**
					 * 值来源
					 * 'from'=>['from'=>'body', 'name'=>'cid'],
					 * 'from'=>'body',
					 * 'body'
					 */
					case 'from':
						$check['from'] =$set['value'] ?? null;
						$check['name'] =$set['name'] ?? $key;
						$source =$set['source'] ?? [];
						$from_set =$set;
						$from_set['throw'] =$_org_set['throw'] ?? null;
						break;
					case 'source':
						$check['from'] ='SET';
						$source =$set ?? [];
						break;
					case 'body':
					case 'query':
					case 'uri':
					case 'header':
					case 'cookie':
						$check['from'] =$set['rule'];
						$from_set =$set;
						$from_set['throw'] =$_org_set['throw'] ?? null;
						if(array_key_exists('name', $set)) $check['name'] =$set['name'];//'query'=>['name'=>'cid']
						break;
					case 'name':
						$check['name'] =$set['value'] ?? $key;
						break;
					/**
					 * 值类型
					 */
					case 'type':
						$check['type'] =$set['value'] ?? 'string';
						$type_set =$set ?? [];
						unset($type_set['rule']);
						unset($type_set['type']);
						break;
					case 'int':
					case 'integer':
					case 'arr':
					case 'array':
					case 'str':
					case 'string':
					case 'json':
					case 'date':
					case 'hex':
					case 'base64':
						$check['type'] =$set['rule'];
						$type_set =$set ?? [];
						unset($type_set['rule']);
						unset($type_set['type']);
						break;
					/**
					 * 默认值
					 */
					case 'default':
						$check['default'] =$set['value'] ?? null;
						$default_set =$set;
						break;
					case 'throw':
					case 'error':
						$check[$set['rule']] =$set[$set['rule']];
						break;
					case 'remove':
					case 'null-remove':
						$check['remove'] =true;
						break;
					default://所有其他后置规则检测
						$valids[$set['rule']] = $set;
						break;
				}
			}
			if('uri'===$check['from']) $check['from'] ='params';//hack this->in
			$source =(is_array($source) && count($source)) ?$source :(is_string($check['from']) ?$this->in[$check['from']] :[]);
			$value =($source)[$check['name']] ?? null;
			$check['value']=$value;
			$remove =false;
			if(null === $value){
				if(!array_key_exists('remove', $check)){
					if(null !==($from_set['throw'] ??null)){
						$check['rule']='from';
						$this->nx_filter_throw($check, $from_set['error'] ?? $check['error'] ?? null, $from_set['throw'] ?? $check['throw'] ?? null);
					}
					if(false !== $default_set){
						if(null === ($default_set['value'] ?? null)){
							$check['rule']='default';
							$this->nx_filter_throw($check, $default_set['error'] ?? $check['error'] ?? null, $default_set['throw'] ?? $check['throw'] ?? null);
						} else $check['value'] =$default_set['value'];
					}
				} else $remove =true;
			}else{//有值，做值类型变换
				switch($check['type']){
					case 'int':
					case 'integer':
						$check['type']='integer';
						$check['value']=(int)$value;
						break;
					case 'json':
						$check['value']=json_decode($value, true);
						$check['rule']='json';
						if('null' !== strtolower($value) && null === $check['value']) $this->nx_filter_throw($check);
						if(count($type_set)){//存在子过滤
							$opts=$type_set;
							$opts['source']=['json'=>$check['value']];
							$check['value']=$this->filter('json', $opts);
						}
						break;
					case 'arr':
					case 'array':
						$check['value']=$value;
						if(is_string($value)){
							$split=$type_set['split'] ?? ',';
							$check['value']=strlen($value) ?((false !== strpos($value, $split)) ?explode($split, $value) :[$value]) :[];
						}
						$check['rule']='array';
						if(!is_array($check['value'])) $this->nx_filter_throw($check);
						if(count($type_set)){//存在子过滤
							$arr_set=[];
							foreach($check['value'] as $_key=>$value){
								$opts=$type_set;
								$opts['source']=$check['value'];
								$arr_set[$_key]=$opts;
							}
							$check['value']=$this->filter($arr_set);
						}
						break;
					case 'hex':
						$check['value']=hexdec($value);
						break;
					case 'base64':
						$check['value']=base64_decode($value, true);
						if(empty($check['value'])) $this->nx_filter_throw($check);
						break;
					case 'date':
						$check['value']=strtotime($value);
						if(!is_array($check['value'])) $this->nx_filter_throw($check);
						break;
					default:
						$check['value']=$value;
						break;
				}
			}
			if(!$remove){
				//开始后置规则检验
				foreach($valids as $rule=>$set){
					$check['rule']=$rule;
					$check['error']=$set['error'] ?? $check['error'] ?? null;
					$check['throw']=$set['throw'] ?? $check['throw'] ?? null;
					$check['check']=$set[$rule] ?? $set['value'] ?? null;
					switch($rule){
						case 'empty-remove':
							$remove=empty($check['value']);
							break;
						case 'callback':
							$callback=$check['check'];
							unset($check['check']);
							$check['value']=call_user_func($callback, $check['value'], function($msg, $code=null, $exception=null) use ($check){
								$check['message']=$msg;
								$this->nx_filter_throw($check, $exception, $code);
							}, $source, $check['name']);
							break;
						case '>0':
							$set['value']=0;
							$check['rule']='>';
						case '>':
							/**
							 * ['rule'=>'>', 'value'=>0, 'throw'=>400],
							 * '>'=>['value'=>0, 'throw'=>400],
							 * '>'=>0,
							 * '>0'=>['throw'=>400],
							 * '>0',
							 */
							if($check['check'] >= $check['value']) $this->nx_filter_throw($check);
							break;
						case '>=':
							if($check['check'] > $check['value']) $this->nx_filter_throw($check);
							break;
						case '<=':
							if($check['check'] < $check['value']) $this->nx_filter_throw($check);
							break;
						case '<':
							/**
							 * ['rule'=>'<', 'value'=>0, 'throw'=>400],
							 * '<'=>['value'=>0, 'throw'=>400],
							 * '<'=>0,
							 */
							if($check['value'] >= $check['check']) $this->nx_filter_throw($check);
							break;
						case '=':
							if($check['value'] != $check['check']) $this->nx_filter_throw($check);
							break;
						case 'len':
						case 'length':
							$check['rule']='length';
							$len=strlen($check['value']);
							if(null !== ($cc=$check['check'] ?? $set['='] ?? null) && $len != $cc){
								$check['rule']='length=';
								$check['check']=$cc;
								$this->nx_filter_throw($check);
							}
							if(null !== ($cc=$set['>'] ?? null) && $len <= $cc){
								$check['rule']='length>';
								$check['check']=$cc;
								$this->nx_filter_throw($check);
							}
							if(null !== ($cc=$set['<'] ?? null) && $len >= $cc){
								$check['rule']='length<';
								$check['check']=$cc;
								$this->nx_filter_throw($check);
							}
							break;
						case 'number':
							$value=trim($check['value']);
							if(!preg_match('/^(\d+)$/', $value)) $this->nx_filter_throw($check);
							break;
						case 'pcre':
						case 'preg':
							$check['rule']='pcre';
							$value=trim($check['value']);
							if(!preg_match($check['check'], $value)) $this->nx_filter_throw($check);
							break;
						case 'mail':
						case 'email':
							if(!preg_match('/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i', $value)) $this->nx_filter_throw($check);
							break;
						case 'china-mobile':
							if(!preg_match('/^[(\d+)|0]?([13|14|15|17|18]\d{9})$/', $value)) $this->nx_filter_throw($check);
							break;
						case 'china-id':
							if(!preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i', $value)) $this->nx_filter_throw($check);
							break;
						case 'ip-v4':
							if(!preg_match('/^(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9])\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|\d)$',
								$value)) $this->nx_filter_throw($check);
							break;
						case 'url':
							if(!preg_match('/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/', $value)) $this->nx_filter_throw($check);
							break;
						default:
							$this->nx_filter_throw($check);
							break;
					}
				}
			}
			if(!$remove) $data[$key] =$check['value'];
		}
		if(in_array('empty', $options)) $options['empty'] =[];
		if(array_key_exists('empty', $options) && 0===count($data)){
			$empty=!is_array($options['empty']) ?['throw'=>$options['empty']]:$options['empty'];
			$empty['rule']='empty';
			$this->nx_filter_throw($empty, $from_set['error'] ?? $empty['error'] ?? null, $from_set['throw'] ?? $empty['throw'] ?? null);
		}
		return $single ?$data[$key] ?? null :$data;
	}


}