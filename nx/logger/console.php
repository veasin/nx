<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/9/5 005
 * Time: 15:14
 */
declare(strict_types=1);
namespace nx\logger;

class console extends dump implements \Psr\Log\LoggerInterface{
	use \Psr\Log\LoggerTrait;
	public function __construct(){
		echo '<script>', "\n";
		echo 'console.group("', \nx\app::$instance->get('uuid'), '");', "\n";
	}
	public function __destruct(){
		echo 'console.groupEnd("', \nx\app::$instance->get('uuid'), '");', "\n";
		echo '</script>', "\n";
	}
	/**
	 * 可任意级别记录日志。
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function log($level, $message, array $context=[]){
		switch($level){
			case 'emergency'://紧急
			case 'alert'://警报
			case 'critical'://临界
			case 'error'://错误
				$level ='error';
				break;
			case 'warning'://警告
				$level ='warn';
				break;
			case 'notice'://通知
				$level ='log';
				break;
			//case 'info'://信息
			//case 'debug'://调试
		}
		echo 'console["', $level, '"](`', $this->interpolate($message, $context), '`);', "\n";
		return $this;
	}
}