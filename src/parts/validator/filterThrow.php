<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/25 025
 * Time: 14:24
 */

namespace nx\parts\validator;

trait filterThrow{
	private function _nx_filter_throw($rule, $key, $value=null, $set=null, $throw=null){
		$default =[
			'code'=>400,
			'exception'=>'\Exception',
			'message'=>[
				'unknown'=>'未知规则错误: {rule}',
				'null'=>'{key} 内容无效',
				'empty'=>'{key} 内容不能为空',
				'callback'=>'{key} 无效内容无法通过自定义检测',
				'length'=>'{key} 值长度不正确',
				'digit'=>'{key} 值大小不正确',
				'match'=>'{key} 无效的内容格式',
			]
		];
		//读取默认配置
		$it=\nx\app::$instance ?? false;
		$setup=(false !==$it) ?($it->setup['filter/throw'] ?? [])+$default :$default;
		//读取抛出设置
		$throw =$throw ?? [];
		if(!array_key_exists('message', $throw)){//自动构建提示消息
			$msg =$setup['message'][$rule] ?? $setup['message']['unknown'] ??'未知类型错误';
			$throw['message']='filter:'.str_replace(['{rule}','{key}'], [$rule, $key], $msg);
		}
		$exception =$throw['exception'] ?? $setup['exception'];
		$exp =new $exception($throw['message'], $throw['value'] ?? $setup['code']);
		$exp->key =$key;
		$exp->rule =$rule;
		$exp->value =$value;
		$exp->set =$set;
		if($it) $it->throw($exp);
		else throw $exp;
	}
	private function _nx_filter_rules_parse($rules){
		//处理非标准写法规则
		$_rules =[];
		foreach($rules as $rule=>$set){
			//字符串规则转换为key=>value
			if(is_int($rule)){//['int']
				if(is_string($set)){//'int'
					$rule =$set;
					switch($rule){
						case 'from':
						case 'default':
							$set =null;
							break;
						default:
							$set=[];
							break;
					}
				} elseif($set instanceof \Closure){//[()=>{}]  todo change 2 is_callable() ?
					$rule ='callback';
					$set =['value'=>$set];
				} elseif(is_array($set)){ // [[]] => ['keys'=>[]]
					$rule ='object';
				} else trigger_error('filter:未知规则格式', E_USER_DEPRECATED); //规则格式错误 开发异常 暂不做处理 待后续识别 => 抛出错误
			}
			if('from' !=$rule && 'default' !=$rule && !is_array($set)) $set =['value'=>$set]; // type:'int', cb:()=>{}  => type:{value:'int'}
			//转换简写规则
			switch(@(string)$rule){
				case 'obj':
				case 'object':
				case 'keys':
				case 'arr':
				case 'array':
				case 'values':
					$_type =['arr'=>'array', 'obj'=>'object'][$rule] ?? $rule;
					$set =['value'=>$_type, 'children'=>$set['value'] ?? $set];
					$rule ='type';
					break;
				case 'json':
					$set['type']=$set['value'] ??[];// json:array => json:{value:array} => json:{type:array}
					$set['value'] =['int'=>'integer', 'uint'=>'unsigned', 'str'=>'string'][$rule] ?? $rule;
					$rule ='type';
					break;
				case 'int':
				case 'uint':
				case 'str':
				case 'integer':
				case 'unsigned':
				case 'string':
				case 'date':
				case 'hex':
				case 'base64':
					$set['value'] =['int'=>'integer', 'uint'=>'unsigned', 'str'=>'string'][$rule] ?? $rule;
					$rule ='type';
					break;
				case 'body':
				case 'query':
				case 'uri':
				case 'header':
				case 'cookie':
					$set =$rule;
					$rule ='from';
					break;
				case 'default':
					$set =['value'=>$rule, 'default'=>$set ??null];
					$rule ='null';
					break;
				case 'throw':
				case 'remove':
					$set =['value'=>$rule];
					$rule ='null';
					break;
				case '=':
				case '>':
				case '<':
				case '!=':
				case '>=':
				case '<=':
					$set =[$rule=>$set['value']];
					$rule ='digit';
					break;
				case 'number':
				case 'email':
				case 'url':
				case 'china-mobile':
				case 'china-id':
				case 'ip-v4':
				case 'ip-v6':
					$set =['value'=>$rule];
					$rule ='match';
					break;
			}
			$_rules[$rule] =$set;
		}
		return $_rules;
	}
	private function _nx_filter_get_value($key, $rules){
		$from =$rules['from'] ?? 'body';
		$real_key =($rules['key'] ??false) ?($rules['key']['value'] ?? $key) :$key;
		return is_string($from) ?$this->in->{$from}($real_key) :($from[$real_key] ?? null);
	}
	private function _nx_filter_change_type($value, $rules){
		$set =$rules['type'] ??[];
		$type =$set['value'] ??'string';
		switch(@(string)$type){
			case 'int':
			case 'integer':// id:{type:'int'}
				$value =is_numeric($value) ?(int)$value :null; //'-123'=>123,'abc'=>null
				break;
			case 'uint':
			case 'unsigned'://
				$value =is_numeric($value) ?(int)$value :null;
				if($value<0) $value =null;
				break;
			case 'json'://id:{type:'json'}, id:{type:{value:'json', children:{}}}
				$value =json_decode(trim($value), true);//if error return null
				if($set['type'] ??false){//type:{value:json, type:int}
					$_rules =['type'=>['value'=>$set['type'], 'type'=>null]] +$rules;
					$value =$this->_nx_filter_change_type($value, $_rules);
				}
				break;
			case 'obj':
			case 'object':
			case 'keys':
				$value =(array)$value;
				if(!is_array($value)) $value =null;
				if(null!==$value && $set['children'] ??false){
					$opts=[]+$set;
					$opts['from'] =$value;
					unset($opts['value'], $opts['children']);
					$_children =$set['children'] ??[];
					$value=$this->filter($_children, $opts); //repeat this()
				}
				break;
			case 'arr':
			case 'array':
			case 'values':
				if(is_string($value)){//'1,2,3,4,5' => [1,2,3,4,5]
					$value =trim($value);
					$split=$type_set['split'] ?? ',';
					$value=strlen($value) ?((false !== strpos($value, $split)) ?explode($split, $value) :[$value]) :[];
				}
				if(!is_array($value)) $value =null;
				if(null !==$value && $set['children'] ??false){
					//整理当前设置作为子元素的父设置
					$opts=[]+$set;
					$opts['from'] =$value;
					unset($opts['value'], $opts['children']);
					//整理key->set
					$key_set=[];
					foreach($value as $_key=>$_un){
						$key_set[$_key]=$set['children'];
					}
					$value=$this->filter($key_set, $opts); //repeat this()
				}
				break;
			case 'hex':
				$value =hexdec(trim($value));//hexdec('z'), 'z'==='0'=>false, 'z'==0 => true, 0=='z' => true
				break;
			case 'base64':
				$value =base64_decode(trim($value), true);
				if(false ===$value) $value =null;
				break;
			case 'date':
				$value=strtotime(trim($value));
				if(false ===$value) $value =null;
				break;
		}
		return $value;
	}
	private function _nx_filter_key_check($value, $rules, $key=''){
		$remove =false;
		foreach(['null', 'empty', 'callback', 'digit', 'length', 'match'] as $rule){
			if($remove) break;
			$set=$rules[$rule] ?? null;
			if(null === $set) continue;
			$check=$value;
			$error =[$rule, $key, $value, $set, $rules['error'] ?? null];
			switch($rule){
				case 'null':
					if(null !== $value) continue;
					if(0===count($set)) $set['value']=[];
					switch(@(string)($set['value'] ?? 'throw')){
						case 'throw':
							$this->_nx_filter_throw(...$error);
							break;
						case 'remove':
							$remove=true;
							break;
						case 'default':
							$value =$set['default'] ?? null;
							break;
						default:
							$value =$set['value'];
					}
					break;
				case 'empty':
					if(!empty($value)) continue;
					switch(@(string)($set['value'] ?? 'throw')){
						case 'throw':
							$this->_nx_filter_throw(...$error);
							break;
						case 'remove':
							$remove=true;
							break;
						case 'default':
							$value =$set['default'] ?? null;
							break;
						default:
							$value =$set['value'];
					}
					break;
				case 'callback'://(value, key, throw(), getValue())
					$value=call_user_func($set['value'], $value, $key, function($msg, $code=null, $exception=null) use ($rules, $key, $value, $set){
						$this->_nx_filter_throw('callback', $key, $value, $set, ['value'=>$code, 'exception'=>$exception, 'message'=>$msg]);
					}, function($key) use($rules){
						return $this->_nx_filter_get_value($key, $rules);
					});
					break;
				case 'length':
					$check=strlen($value);
				case 'digit':
					$set['=']=$set['='] ?? $set['value'] ?? null;
					if(null !==($set['='] ?? null) && $check != $set['=']) $this->_nx_filter_throw(...$error);
					if(null !==($set['>'] ?? null) && $check <= $set['>']) $this->_nx_filter_throw(...$error);
					if(null !==($set['<'] ?? null) && $check >= $set['<']) $this->_nx_filter_throw(...$error);
					if(null !==($set['!='] ?? null) && $check == $set['!=']) $this->_nx_filter_throw(...$error);
					if(null !==($set['<='] ?? null) && $check > $set['<=']) $this->_nx_filter_throw(...$error);
					if(null !==($set['>='] ?? null) && $check < $set['>=']) $this->_nx_filter_throw(...$error);
					break;
				case 'match':
					$check=$set['value'] ?? false;
					if(!$check) continue;
					$result=false;
					switch(@(string)$check){
						case 'number':
							$result=preg_match('/^(\d+)$/', $value);
							break;
						case 'email':
							$result=filter_var($value, FILTER_VALIDATE_EMAIL);
							break;
						case 'url':
							$result=filter_var($value, FILTER_VALIDATE_URL);
							break;
						case 'china-mobile':
							$result=preg_match('/^[(\d+)|0]?([13|14|15|17|18]\d{9})$/', $value);
							break;
						case 'china-id':
							$result=preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i', $value);
							break;
						case 'ip-v4':
							$result=filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
							break;
						case 'ip-v6':
							$result=filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
							break;
						default:
							if(false !== $check) $result=preg_match($check, $value);
							break;
					}
					if(!$result) $this->_nx_filter_throw(...$error);
					break;
			}
			//unset($rules[$rule]);
		}
		return [$remove,$value];
	}
	private function _nx_filter_whole_check($data, $options=[]){
		//'whole-empty'
		if(array_key_exists('whole-empty', $options) && 0===count($data)){
			$set =$options['whole-empty'] ??[];
			switch(@(string)($set['value'] ?? 'throw')){
				case 'throw':
					$this->_nx_filter_throw('whole-empty', null, [], $set, $options['error'] ?? null);
					break;
				case 'default':
					$data =$set['default'] ?? null;
					break;
				default:
					$data =$set['value'];
			}
		}
		return $data;
	}
	/**
	 * 过滤器，对输入进行过滤。可指定输入内容来源或设置来源数组。
	 * ('id', ['int']) => (['id'=>['int']])
	 * (['id'=>['int']])//第1参数默认以 type:object=>children解析
	 * 采用key=>value形式设置规则，规则的结构也是key=>value形式设置。规则列表如下：
	 * 规则{throw}:异常抛出设定
	 * error:{value:400, message:'错误提示信息', exception:'\Exception'},
	 *      $exception =new \nx\Exception\filter\format();
	 *      $exception->rule =$rule;
	 * error:$exception,
	 * error:400, =>error:{value:400},
	 * 规则{from}:数据来源 如果不设定，不存在，值为 null
	 * from:'[body|query|uri|header|cookie]', //$this->in->$from($key)
	 * from:{},//数据来源对象，如 $this->in
	 * 规则{key}:指定数据来源中的key
	 * key:'nid', //输出结果中key对应的来源数据中真实key
	 * 规则{type}:数据类型 如果类型或格式不正确，值为 null
	 * 不推荐直接使用type，而是使用具体类型替代，如 'integer'
	 * - type:'[integer|unsigned|string|array|object|json|date|hex|base64]',
	 * - type:'[int    |uint    |str   |arr  |obj   |json|date|hex|base64]',//简化设置写法
	 * - type:{value:'[array(values)|object(keys)]', children:{
	 * -      key:set[],// array 可省略key，即从 0开始
	 * - }}
	 * {'json':'integer'} // json格式的字符串，最终值为整形
	 * {'array':'integer'} //无key数组类型，内容是整形
	 * {'object':{n:'integer'}} //当前数据为key-value形式数组，其中key为n的值为整数
	 * values同array，keys同object
	 * 假定取值:{
	 *      user:{
	 *          id:3
	 *      },
	 *      users:[
	 *          {
	 *              id:1
	 *          },
	 *          {
	 *              id:2
	 *          }
	 *      ]
	 * }
	 * 设置规则:{
	 *      user:{'keys':{
	 *          id:'integer'
	 *      }}
	 *      users:{'values':{
	 *          {'keys':{
	 *              id:'integer',
	 *          }}
	 *      }}
	 * }
	 * 规则{null}:如果值为null的处理
	 * null:{value:'[throw|remove|default]', default:1}, // throw 抛出异常 remove 从结果中移除对应key default 立刻返回默认值
	 * null:'[throw|remove]', //上面简化写法
	 * null:'abc'|123|[], //default的简化写法，必定要排除 'remove' 'throw'
	 * 规则{empty}:如果值为empty的处理 empty <= 0, '', false, [], {}
	 * empty:同null设定
	 * 规则{callback}:使用自定义回调来检测取值, callback($value, $key, $source=[])
	 * callback:()=>[], //通过is_callable()检测的取值
	 * 规则{digit}:对值的结果进行数字比较
	 * digit:{'=':3, '<':10, '>':0, '!=':5, '>=':0, '<=':10},
	 * digit:3, // => {'=':3}
	 * 规则{length}:对值进行字符长度比较
	 * length:同digit设定
	 * 规则{match}:对值进行字符匹配检测
	 * match:'[number|email|url|china-mobile|china-id|ip-v4|ip-v6]',
	 *      number:数字规则 '0001' 123,
	 *      email:电子邮件规则
	 *      url:网址规则
	 *      china-mobile:中国手机号
	 *      china-id:中国身份证号
	 *      ip-v4:ipv4规则 255.255.255.255
	 *      ip-v6:ipv6规则
	 * match:'#^\d+$#', //如不在上述规则列表中即为正则匹配检测
	 * @param array|string $vars    obj->children{}规则设置。 当$vars为字符时，会转换成obj->children{$vars=>$options}
	 * @param array        $options 全局规则配置，缺省规则设置
	 * @return array|mixed|null
	 */
	public function filter($vars=[], $options=[]){
		$data =[];
		$single =false;
		if(is_string($vars)){//单字段模式，转换为obj->children:{} filter('id', [], []) =>filter(['id'=>[], [])
			$single =$vars;
			$vars =[$vars=>$options];
			$options =[];
		} else $options =$this->_nx_filter_rules_parse($options);
		foreach($vars as $key=>$rules){//开始循环解析规则
			if(!is_array($rules)) $rules =[$rules];//'int'  =>['int']
			$rules =$this->_nx_filter_rules_parse($rules);
			$rules =$rules+ $options;//合并规则设置 no array_merge 防止数字字符key
			//处理非标准写法规则
			//获取结果
			$value =$this->_nx_filter_get_value($key, $rules);
			//not null 开始处理值的类型
			if(null !==$value) $value =$this->_nx_filter_change_type($value, $rules);
			//开始后置规则检验
			$set =$this->_nx_filter_key_check($value, $rules, $key, $rules['from'] ?? 'body');
			//如果不设定移除，那么返回结果
			if(!$set[0]) $data[$key] =$set[1];
		}
		$data =$this->_nx_filter_whole_check($data, $options);
		return false !==$single ?$data[$single] ?? null :$data;
	}
	/**
	 * 针对值做过滤，不包含取值逻辑
	 * @param mixed $value
	 * @param array $rules
	 * @return null
	 */
	public function filterValue($value, $rules=[]){
		if(!is_array($rules)) $rules =[$rules];//'int'  =>['int']
		$rules =$this->_nx_filter_rules_parse($rules);
		//处理非标准写法规则
		//not null 开始处理值的类型
		if(null !==$value) $value =$this->_nx_filter_change_type($value, $rules);
		//开始后置规则检验
		$set =$this->_nx_filter_key_check($value, $rules, '', $rules['from'] ?? 'body');
		return !$set[0] ?$set[1] :null;
	}
}