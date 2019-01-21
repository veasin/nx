<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/08/23 023
 * Time: 01:01
 */
declare(strict_types=1);
namespace nx;

use nx\structure\initialize;

/**
 * Class log [psr-3]
 * @package nx
 */
class logger implements \Psr\Log\LoggerAwareInterface, \Psr\Log\LoggerInterface{
	use \Psr\Log\LoggerTrait, initialize;
	public function __construct($setup=[]){
		$this->buffer =$setup;
		$this->initialize();

	}
	/**
	 * 设置一个日志记录实例
	 * @param \Psr\Log\LoggerInterface $logger
	 * @return $this
	 */
	public function setLogger(\Psr\Log\LoggerInterface $logger){
		//$this->initialize();
		$this->clients[]=$logger;
		return $this;
	}
	/**
	 * 可任意级别记录日志。
	 * @param        $var
	 * @param string $template
	 * @param string $level
	 * @return \nx\logger\logger
	 */
	public function __invoke($var, $template='{var}', $level='info'){
		//$this->initialize();
		if(array_key_exists($var, $this->clients)){
			return $this->clients[$var];
		} else return $this->log($level, '{var}',['var'=>$var]);
	}
	/**
	 * 可任意级别记录日志。
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function log($level, $message, array $context=[]){
		//$this->initialize();
		foreach($this->clients as $logger){
			$logger->log($level, $message, $context);
		}
		return $this;
	}
}