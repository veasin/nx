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
class file extends dump implements \Psr\Log\LoggerInterface{
	use \Psr\Log\LoggerTrait;
	private $path='';
	private $file='log.log';
	private $level=[
		''=>true,//兼容
		'emergency'=>true,
		'alert'=>true,
		'critical'=>true,
		'error'=>true,
		'warning'=>true,
		'notice'=>true,
		'info'=>true,
		'debug'=>true,
	];
	private $start=0;
	private $handles=[];
	private $uuid ='nx';
	protected $defIndent ='{uuid} {micro+} ';
	public function __construct($setup=[]){
		$this->path=realpath($setup['path'] ?? \nx\app::$instance->getPath('logs')).DIRECTORY_SEPARATOR;
		$this->file=$setup['file'] ?? date('Ymd').'.log';
		$this->uuid =$setup['uuid'] ??\nx\app::$instance->getUUID();
		if(array_key_exists('level', $setup)) $this->level=$setup['level'] + $this->level;
		$this->start=microtime(true);
		$this->log('',"{datetime}:[{method}]{uri}",  [
			'datetime'=>date('Y-m-d H:i:s'),
			'method'=>$_SERVER['REQUEST_METHOD'],
			'uri'=>$_SERVER['REQUEST_URI'],
		]);
	}
	private function write($level, $string, $end="\n"){
		$file =is_string($this->level[$level]) ?$this->level[$level] :$this->file;
		if(!array_key_exists($file, $this->handles)) $this->handles[$file]=@fopen($this->path.$file, 'a');
		if($this->handles[$file]) fwrite($this->handles[$file], $string.$end);
	}
	/**
	 * 可任意级别记录日志。
	 * @param mixed  $level
	 * @param string $message
	 * @param array  $context
	 * @return $this
	 */
	public function log($level, $message, array $context=[]){
		if($this->level[$level] && $this->path){
			$context['micro+'] =sprintf("%06.2fms", (microtime(true) - $this->start) * 1000);
			$context['uuid'] =$this->uuid;
			$indent =$this->defIndent;
			if('' !==$level){
				$indent .='[{level}] ';
				$context['level'] =$level;
			}
			$this->write($level, $this->interpolate($indent.$message, $context));
		}
		return $this;
	}
	public function __destruct(){
		$this->log('', "end.\n");
	}
}