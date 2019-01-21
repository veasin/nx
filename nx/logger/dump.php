<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/08/23 023
 * Time: 00:58
 */
declare(strict_types=1);
namespace nx\logger;

/**
 * Class file [psr-3 Log https://laravel-china.org/docs/psr/psr-3-logger-interface]
 * @package nx\log
 */
class dump implements \Psr\Log\LoggerInterface{
	//use \Psr\Log\LoggerTrait;
	protected $defaultContext=[];
	protected function interpolate($message, array $context=[]){
		$replace=[];
		$context+=$this->defaultContext;
		foreach($context as $key=>$val){
			// 检查该值是否可以转换为字符串
			if(!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))){
				$replace['{'.$key.'}']=$val;
			}
		}
		return strtr($message, $replace);
	}
	/**
	 * 可任意级别记录日志。
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function log($level, $message, array $context=[]){
		echo '[', $level, ']';
		var_dump($this->interpolate($message, $context));
		return $this;
	}
	/**
	 * 系统无法使用。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function emergency($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::EMERGENCY, $message, $context);
	}
	/**
	 * 必须立即采取行动。
	 * 例如: 整个网站宕机了，数据库挂了，等等。 这应该
	 * 发送短信通知警告你.
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function alert($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::ALERT, $message, $context);
	}
	/**
	 * 临界条件。
	 * 例如: 应用组件不可用，意外的异常。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function critical($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::CRITICAL, $message, $context);
	}
	/**
	 * 运行时错误不需要马上处理，
	 * 但通常应该被记录和监控。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function error($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::ERROR, $message, $context);
	}
	/**
	 * 例外事件不是错误。
	 * 例如: 使用过时的API，API使用不当，不合理的东西不一定是错误。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function warning($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::WARNING, $message, $context);
	}
	/**
	 * 正常但重要的事件.
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function notice($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::NOTICE, $message, $context);
	}
	/**
	 * 有趣的事件.
	 * 例如: 用户登录，SQL日志。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function info($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::INFO, $message, $context);
	}
	/**
	 * 详细的调试信息。
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function debug($message, array $context=[]){
		return $this->log(\Psr\Log\LogLevel::DEBUG, $message, $context);
	}
}