<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/18 018
 * Time: 13:40
 */

namespace nx\mvc\traits;

use nx\base\callApp;

/**
 * Trait model
 * @package nx\mvc\traits
 * @deprecated 2019-04-17
 */
trait model{
	use callApp;
	public function __construct(...$setup){
		$this->app = \nx\app::$instance;
		//$this->initTraits();
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
	protected $lastError =[1, '未知错误'];
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
}