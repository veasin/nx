<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/12 012
 * Time: 09:13
 */
namespace nx\helpers\network\context;

class response{
	/**
	 * @var \resource
	 */
	private $stream=null;
	private $statusCode=0;
	private $statusMessage='';
	private $protocols='';
	private $protocolsVersion='';
	private $working=false;
	private $meta=[];
	private $originHeaders=[];
	private $headers=[];
	private $body=null;
	/**
	 * response constructor.
	 * @param null $stream
	 */
	public function __construct($stream=null){
		$this->working=is_resource($stream) && 'stream' === get_resource_type($stream);
		$this->stream=$stream;
		if($this->working) $this->parseStream();
	}
	public function __destruct(){
		fclose($this->stream);
	}
	protected function parseStream(){
		$this->meta=stream_get_meta_data($this->stream);
		switch($this->meta['wrapper_type']){
			case 'http':
				$headers=$this->meta['wrapper_data'];
				$http=array_shift($headers);
				list($protocols, $code, $this->statusMessage)=explode(' ', $http, 3);
				list($this->protocols, $this->protocolsVersion)=explode('/', $protocols, 2);
				$this->statusCode=(int)$code;
				foreach($headers as $header){
					list($key, $value)=explode(': ', $header);
					$this->originHeaders[$key]=$value;
					$this->headers[strtolower($key)]=$value;
				}
				break;
			default:
				$this->working=false;
				break;
		}
	}
	/**
	 * 是否已超时
	 * 如果在上次调用 fread() 或者 fgets() 中等待数据时流超时了则为 TRUE。
	 * @return bool
	 */
	public function hasTimeout(){
		return ($this->working && $this->meta['timed_out']) || false;
	}
	/**
	 * 输出json格式
	 * @param bool $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
	 * @return mixed
	 */
	public function asJson(bool $assoc=true){
		return $this->working ?json_decode($this->body(), $assoc) :null;
	}
	/**
	 * 响应的状态码 0 为响应前或响应解析错误
	 * @return int
	 */
	public function status():int{
		return $this->statusCode;
	}
	/**
	 * 响应的状态消息
	 * @return string
	 */
	public function statusMessage():string{
		return $this->statusMessage;
	}
	/**
	 * 响应的文本
	 * @return string
	 */
	public function body():?string{
		if($this->working && null === $this->body) $this->body=stream_get_contents($this->stream);
		return $this->body;
	}
	/**
	 * 获取响应头字段
	 * @param string $name       头字段名称
	 * @param bool   $fromOrigin 是否从原始值里面取
	 * @return null|string
	 */
	public function header(string $name, bool $fromOrigin=false):?string{
		return (!$fromOrigin ?$this->headers() :$this->originHeaders)[$name] ?? null;
	}
	/**
	 * 获取全部响应头
	 * @param bool $fromOrigin 是否为原始值
	 * @return array|null
	 */
	public function headers(bool $fromOrigin=false):?array{
		return !$fromOrigin ?$this->headers() :$this->originHeaders;
	}
}