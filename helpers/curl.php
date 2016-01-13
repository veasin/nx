<?php
namespace nx\helpers;

class curl{
	private $handle = null;
	private $response = null;
	public $opts = [];
	private $headers=[];

	private $log =0;
	private $method ="GET";
	public static function factory($url, $method = 'GET', $args = [], $log =0){
		return new self($url, $method, $args, $log);
	}
	public function __construct($url, $method = 'GET', $args = [], $log =0){
		$this->url($url);
		$this->method($method, $args);
		$this->log =$log;
		return $this;
	}
	public function __destruct(){
		if(!is_null($this->handle)) curl_close($this->handle);
	}
	/**
	 * 设定日志输出等级，默认不输出任何日志
	 * @param int $log_level
	 * @return $this
	 */
	public function log($log_level=1){
		$this->log =$log_level;
		return $this;
	}
	/**
	 * 提交文件
	 * @param $Filename
	 * @param null $Minetype
	 * @param null $Postname
	 * @return \CURLFile
	 */
	public function file($Filename, $Minetype = null, $Postname = null){
		return curl_file_create($Filename, $Minetype, $Postname);
	}
	/**
	 * 设定请求地址
	 * @param $url
	 * @return $this
	 */
	public function url($url){
		$this->opts[CURLOPT_URL] = $url;
		return $this;
	}
	/**
	 * 直接post方式请求
	 * @param $Data
	 * @return $this
	 */
	public function post($Data){
		$this->method('POST', $Data);
		return $this;
	}
	/**
	 * 直接post方式请求，不使用array
	 * @param $Data
	 * @return $this
	 */
	public function postF($Data){
		$this->method('POSTF', $Data);
		return $this;
	}
	/**
	 * 直接get方式请求
	 * @return $this
	 */
	public function get(){
		$this->method('GET');
		return $this;
	}
	/**
	 * 请求方法，设定请求方式和请求数据
	 * @param string $method
	 * @param array $data
	 * @return $this
	 */
	public function method($method = 'GET', $data = []){
		$this->method =strtoupper($method);
		switch($this->method){
			case 'GET':
				$this->opts[CURLOPT_HTTPGET] = 1;
				if(is_array($data) && !empty($data)) $this->opts[CURLOPT_URL] .=((isset($this->opts[CURLOPT_URL]) && strpos($this->opts[CURLOPT_URL], '?') !==false) ?'&' :'?') . http_build_query($data);
				break;
			case 'POST':
				$this->opts[CURLOPT_POST] = 1;
				if(is_array($data)) $data =http_build_query($data);
				$this->opts[CURLOPT_POSTFIELDS] = $data;
				unset($this->opts[CURLOPT_HTTPGET]);
				break;
			case 'POSTF':
				$this->opts[CURLOPT_POST] = 1;
				$this->opts[CURLOPT_POSTFIELDS] = $data;
				unset($this->opts[CURLOPT_HTTPGET]);
				break;
			case 'PUT':
			case 'DELETE':
			case 'HEAD':
			case 'OPTIONS':
			default:
				$this->opts[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
				if(is_array($data)){
					$data =http_build_query($data);
					//$this->opts[CURLOPT_URL] .=((isset($this->opts[CURLOPT_URL]) && strpos($this->opts[CURLOPT_URL], '?') !==false) ?'&' :'?') . $data;
				}
				$this->opts[CURLOPT_POSTFIELDS] = $data;
				unset($this->opts[CURLOPT_HTTPGET]);
				//if(is_array($data) && !empty($data)) $this->opts[CURLOPT_URL] .=((isset($this->opts[CURLOPT_URL]) && strpos($this->opts[CURLOPT_URL], '?') !==false) ?'&' :'?') . http_build_query($data);
				break;
		}
		return $this;
	}
	/**
	 * 添加请求头
	 * @param $header
	 * @return $this
	 */
	public function httpHeader($header){
		if(is_string($header)){
			$this->headers[] =$header;
		} elseif(is_array($header)){
			$this->headers =array_merge($this->headers, $header);
		}
		return $this;
	}
	/**
	 * @param bool $has 是否输出头信息 string 添加自定义头 array 添加一堆自定义头
	 * @return $this
	 */
	public function header($has = true){
		if(is_bool($has)){
			$this->opts[CURLOPT_HEADER] =$has ?1 :0;
		} elseif(is_string($has)){
			$this->headers[] =$has;
		} elseif(is_array($has)){
			$this->headers =array_merge($this->headers, $has);
		}
		return $this;
	}
	/**
	 * 禁止输出响应body
	 * @param int $has
	 * @return $this
	 */
	public function nobody($has = 1){
		$this->opts[CURLOPT_NOBODY] = $has;
		return $this;
	}
	/**
	 * 设定超时
	 * @param int $MS
	 * @return $this
	 */
	public function timeout($MS = 0){
		if($MS >0) $this->opts[CURLOPT_TIMEOUT] = $MS;
		else unset($this->opts[CURLOPT_TIMEOUT]);
		return $this;
	}
	/**
	 * 设定访问agent
	 * @param $User
	 * @return $this
	 */
	public function agent($User){
		$this->opts[CURLOPT_USERAGENT] = $User;
		return $this;
	}
	/**
	 * 设定用户名和密码
	 * @param $username
	 * @param string $password
	 * @return $this
	 */
	public function user($username, $password=''){
		$this->opts[CURLOPT_USERPWD] =$username.':'.$password;
		return $this;
	}
	/**
	 * 设定cookie
	 * @param array $Data
	 * @param bool|true $UnsetSID
	 * @return $this
	 */
	public function cookie($Data = [], $UnsetSID = true){
		$c = $_COOKIE;
		if(!empty($Data)){
			$c = $Data;
		}
		if($UnsetSID) unset($c['PHPSESSID']);
		$cs = '';
		foreach($c as $key => $value){
			$cs .= $key . '=' . $value . '; ';
		}
		$this->opts[CURLOPT_COOKIE] = $cs;
		return $this;
	}
	/**
	 * 设定响应范围
	 * @param $d1
	 * @param $d2
	 * @return $this
	 */
	public function range($d1, $d2){
		$this->opts[CURLOPT_RANGE] = $d1.'-'. $d2;
		return $this;
	}
	public function set($key, $value){
		$this->opts[$key] = $value;
		return $this;
	}
	/**
	 * 原始响应内容
	 * @return null|string
	 */
	public function RW(){
		return is_string($this->response) ?$this->response :'';
	}
	public function rwCookie(){
		preg_match_all('|Set-Cookie: (.*);|U', $this->response, $matches);
		return $matches[1];
	}
	/**
	 * 按照设定开始访问并获取内容
	 * @param bool|true $ReturnTransfer
	 * @return $this
	 */
	public function exec($ReturnTransfer = true){
		if($ReturnTransfer){
			$this->opts[CURLOPT_BINARYTRANSFER] = 1;
			$this->opts[CURLOPT_RETURNTRANSFER] = 1;
		}
		$this->handle = curl_init();
		if(!empty($this->headers)) $this->opts[CURLOPT_HTTPHEADER] = $this->headers;
		@curl_setopt_array($this->handle, $this->opts);
		if($this->log)
		\nx\app::$instance->log('curl '.strtolower($this->method).': '.$this->opts[CURLOPT_URL].' '.(
			isset($this->opts[CURLOPT_POSTFIELDS])
				?json_encode($this->opts[CURLOPT_POSTFIELDS], JSON_UNESCAPED_UNICODE)
				:'[]'
			).'');
		$start =microtime(true);
		$this->response = curl_exec($this->handle);
		if($this->log>2) \nx\app::$instance->log(' - response:'.$this->RW());
		if($this->log>1) \nx\app::$instance->log(sprintf(' - time: %0.3fms', microtime(true)-$start));
		return $this;
	}
	/**
	 * 返回本次请求信息
	 * @param string $Opt //"url" "content_type" "http_code" "header_size" "request_size" "filetime" "ssl_verify_result" "redirect_count" "total_time" "namelookup_time" "connect_time" "pretransfer_time" "size_upload" "size_download" "speed_download" "speed_upload" "download_content_length" "upload_content_length"  "redirect_time"
	 * @return string
	 */
	public function info($Opt = null){
		if(is_null($Opt)){
			return curl_getinfo($this->handle);
		}
		else return curl_getinfo($this->handle, $Opt);
	}
	/**
	 * 解析响应内容为json数据
	 * @return mixed
	 */
	public function parse(){
		if(is_null($this->handle)) $this->exec(true);
		$r = $this->RW();
		return json_decode($r, true);
	}
	/**
	 * 基本认证
	 * @param $user
	 * @param $password
	 * @return curl
	 */
	public function authorization_basic($user, $password){
		return $this->authorization('basic', base64_encode($user.':'.$password));
	}
	/**
	 * 认证
	 * @param string $type 类型
	 * @param string $token
	 * @return curl
	 */
	public function authorization($type, $token){
		$type =ucfirst(strtolower($type));
		return $this->httpHeader("Authorization: {$type} {$token}");
	}
}