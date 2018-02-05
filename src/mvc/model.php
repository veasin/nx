<?php
namespace nx\mvc;

class model{
	/**
	 * @var \nx\app
	 */
	public $app = null;
	public $buffer =[];
	protected $lastError =[1, '未知错误'];
	public function __construct(...$setup){
		$this->app = \nx\app::$instance;
		//初始化引入的trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}
		//如果本身存在init方法，那么就立刻执行
		if(method_exists($this, 'init')) $this->init(...$setup);
	}
	/**
	 * 实例化model并缓存
	 * @param array ...$setup
	 * @return static
	 */
	static public function instance(...$setup){
		$c = get_called_class();
		return new $c(...$setup);
	}
	/**
	 * 无参数返回最后的错误信息，需要在model中提前指定，否则为设定最后错误编号和注释，同时会写入日志
	 * @see return $this->lastError(1,''未知错误);
	 * @param int    $code 记录日志的错误编码
	 * @param string $message 记录日志的消息内容
	 * @param bool   $return 默认返回值 false
	 * @return array|bool
	 */
	public function lastError($code=0, $message='', $return=false){
		if(func_num_args() >0){
			$this->lastError =[$code, $message];
			$this->log('model error: '.$code.' - '.$message);
			return $return;
		}
		return $this->lastError;
	}
	/**
	 * 魔术方法，所有未知方法调用都转发到app上
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	public function __call($name, $args){
		return call_user_func_array([$this->app, $name], $args);
	}
}
