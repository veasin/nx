<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/06/05 005
 * Time: 17:39
 */

namespace nx\helpers;

class validator{
	private static $msg_map=[
		'not_empty'=>'%s 不能为空',
		'length'=>'%s 长度不符',
		'equal'=>'%s 内容不符',
		'same'=>'%s 内容不同',
		'min_length'=>'%s 长度不能少于 %d 个字符',
		'max_length'=>'%s 长度不能多于 %d 个字符',
		'numeric'=>'%s 不是数字',
		'regex'=>'%s 不符合规则',
		'chinese'=>'%s 不全是中文字符',
		'qq'=>'%s 不是正确的qq号码',
		'email'=>'%s 不是正确email格式',
		'phone'=>'%s 不是正确固定电话格式',
		'mobile'=>'%s 不是正确手机号码格式',
		'id_card'=>'%s 不是正确的格式',
		'date'=>'%s 不是正确的日期格式',
		'url'=>'%s 不是正确的网址',
		'ip'=>'%s 不是正确的ip格式',
	];
	private static $name_map=[
		'name'=>'用户名',
		'password'=>'密码',
		'mobile'=>'手机号码',
	];
	const not_empty='not_empty';//是否必填
	const length='length';//指定长度
	const equal='equal';//完全相等
	const same='same';//和数据中的某个完全相同
	const min_length='min_length';//最小长度
	const max_length='max_length';//最大长度
	const numeric='numeric';//数字
	const regex='regex';//正则
	const chinese='chinese';//中文
	const qq='qq';//QQ号
	const mail='mail';//验证邮箱
	const email='email';//验证邮箱
	const phone='phone';//验证固定电话
	const mobile='mobile';//手机号
	const id_card='id_card';//验证身份证
	const date='date';//日期
	const url='url';//网址
	const ip='ip';//ip
	private $errs=[];
	private $rules=[];
	private $data=[];
	private $result=[];
	private $names=[];
	private $msgs=[];
	/**
	 * validator constructor.
	 * @param string $from
	 * @param array $name
	 * @param array $msg
	 */
	public function __construct($from='POST', $name=[], $msg=[]){
		if(is_string($from)){
			switch(strtolower($from)){
				case 'post':
					$this->data=$_POST;
					break;
				case 'get':
					$this->data=$_GET;
					break;
				case 'put':
				case 'delete':
				case 'input':
					$this->data=$this->getInput();
					break;
			}
		}else $this->data=$from;
		$this->names=array_merge(self::$name_map, 0!==count($name) ?$name :[]);
		$this->msgs=array_merge(self::$msg_map, 0!==count($msg) ?$msg :[]);
	}
	/**
	 * 从input中直接解析为变量
	 * @return mixed
	 */
	private function getInput(){
		parse_str(file_get_contents('php://input'), $vars);
		return $vars;
	}
	/**
	 * 添加验证规则
	 * @param $name
	 * @param array ...$args
	 * @return $this
	 */
	public function add($name, ...$args){
		if(is_string($name)){
			if(!array_key_exists($name, $this->rules)) $this->rules[$name]=[];
			$this->rules[$name][]=$args;
		}elseif(is_array($name)){
			foreach($name as $_name=>$rule){
				if(is_numeric($_name)){
					$_name =array_shift($rule);
				}
				if(is_string($rule)) $rule =[$rule];
				if(!array_key_exists($_name, $this->rules)) $this->rules[$_name]=[];
				$this->rules[$_name][]=$rule;
			}
		}
		return $this;
	}
	/**
	 * 处理错误并返回信息
	 * @param $name
	 * @param $msg
	 * @param array ...$args
	 * @return null
	 */
	private function error($name, $msg, ...$args){
		array_unshift($args, array_key_exists($msg, $this->msgs) ?$this->msgs[$msg]
			:$msg, array_key_exists($name, $this->names) ?$this->names[$name] :$name);
		$this->errs[]=call_user_func_array('sprintf', $args);
		return null;
	}
	/**
	 * 检查规则
	 * @param $name
	 * @param $rule
	 * @return bool|null
	 */
	private function check($name, $rule){
		$valid=array_shift($rule);
		if(null ===$valid) $valid=self::not_empty;
		if(is_string($valid)){
			switch(strtolower($valid)){
				case self::not_empty:
					$has=count($rule);
					if(empty($this->result[$name])) return $has ?$rule[0] :$this->error($name, $valid);
					break;
				case self::length:
					$len=(int)$rule[0];
					if($len>0 && (null===$this->result[$name] || strlen($this->result[$name])!==$len)) return $this->error($name, $valid, $len);
					break;
				case self::min_length:
					$len=(int)$rule[0];
					if($len>0 && (null===$this->result[$name] || strlen($this->result[$name])<$len)) return $this->error($name, $valid, $len);
					break;
				case self::max_length:
					$len=(int)$rule[0];
					if($len>0 && (null===$this->result[$name] || strlen($this->result[$name])>$len)) return $this->error($name, $valid, $len);
					break;
				case self::equal:
					if($rule[0] && (null===$this->result[$name] || $this->result[$name]!==$rule[0])) return $this->error($name, $valid);
					break;
				case self::same:
					if($rule[0] && (null===$this->result[$name] || !array_key_exists($rule[0], $this->data) || $this->result[$name]!==$this->data[$rule[0]])) return $this->error($name, $valid);
					break;
				case self::numeric:
					if(null===$this->result[$name] || preg_match('/^(\d+)$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::regex:
					if($rule[0] && (null===$this->result[$name] || preg_match($rule[0], $this->result[$name])===0)) return $this->error($name, $valid);
					break;
				case self::chinese:
					if(null===$this->result[$name] || preg_match('/^[\x{3400}-\x{4db5}|\x{4e00}-\x{9fa5}|\x{f900}-\x{fa2c}]+$/iu', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::qq:
					if(null===$this->result[$name] || preg_match('/^(\d{5,11})$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::mail:
				case self::email:
					if(null===$this->result[$name] || preg_match('/^[\w\d]+[\w\d-.]*@[\w\d-.]+\.[\w\d]{2,10}$/i', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::phone:
					if(null===$this->result[$name] || preg_match('/^0\d{2,3}[-]?\d{7,8}$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::mobile:
					if(null===$this->result[$name] || preg_match('/^[(\d+)|0]?(13\d{9})|(15\d{9})|(18\d{9})|(17\d{9})$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::id_card:
					if(null===$this->result[$name] || preg_match('/^\d{6}((1[89])|(2\d))\d{2}((0\d)|(1[0-2]))((3[01])|([0-2]\d))\d{3}(\d|X)$/i', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::date:
					if(array_key_exists($name, $this->result)){
						$time=strtotime($this->result[$name]);
						if($time!==false) return $time;
					}
					return $this->error($name, $valid);
					break;
				case self::url:
					if(null===$this->result[$name] || preg_match('/^(http:\/\/)?(https:\/\/)?([\w\d-]+\.)+[\w-]+(\/[\d\w-.\/?%&=]*)?$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				case self::ip:
					if(null===$this->result[$name] || preg_match('/^(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9])\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|[1-9]|0)\.(25[0-5]|2[0-4]\d|[0-1]{1}\d{2}|[1-9]{1}\d{1}|\d)$/', $this->result[$name])===0) return $this->error($name, $valid);
					break;
				default:
					return $this->error($name, '未识别验证规则 %s');
					break;
			}
		}elseif(is_callable($valid)){
			$r=call_user_func($valid, $this->result[$name]);
			if($r!==true) return $this->error($name, $r);
		}
		return true;
	}
	/**
	 * 开始验证规则
	 * @return array|bool
	 */
	public function start(){
		$this->result=[];
		foreach($this->rules as $name=>$rules){
			$this->result[$name]=isset($this->data[$name]) ?$this->data[$name] :null;
			foreach($rules as $rule){
				$r=$this->check($name, $rule);
				if(null===$r) return false;
				if($r!==true) $this->result[$name]=$r;
			}
		}
		return $this->result;
	}
	/**
	 * 返回第一个错误
	 * @return mixed
	 */
	public function lastError(){
		return $this->errs[0];
	}
}