<?php
namespace nx\network;

class curl{
	const CONTENT_TYPE_TEXT ='text/plain';
	const CONTENT_TYPE_HTML ='text/html';
	const CONTENT_TYPE_FORM ='multipart/form-data';
	const CONTENT_TYPE_JSON ='application/json';
	const CONTENT_TYPE_URL ='application/x-www-form-urlencoded';

	private $handle = null;
	private $response = null;
	public $opts = [];
	private $headers=[];
	//?a=b
	private $query=[];
	private $data=null;

	private $logLevel =0;
	private $method ="GET";
	private $contentType =self::CONTENT_TYPE_URL;
	public $status =0;

	public static function factory($url, $method = 'GET', $body =null, $query=[], $log =0){
		if(is_int($query)){
			$log =$query;
			$query =[];
		}
		return new self($url, $method, $body, $query, $log);
	}
	public function __construct($url, $method = 'GET', $body = null, $query=[], $log =0){
		$this->url($url);
		$this->method($method, $body, $query);
		$this->logLevel =$log;
	}
	public function __destruct(){
		if(!is_null($this->handle)) curl_close($this->handle);
	}
	/**
	 * 设定日志输出等级，默认不输出任何日志
	 * @param int $logLevel
	 * @return $this
	 */
	public function log($logLevel=1){
		$this->logLevel =$logLevel;
		return $this;
	}
	/**
	 * 提交文件
	 * @param $filename
	 * @param null $mineType
	 * @param null $postName
	 * @return \CURLFile
	 */
	public function file($filename, $mineType = null, $postName = null){
		return curl_file_create($filename, $mineType, $postName);
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
	 * @param $data
	 * @return $this
	 */
	public function post($data =null){
		$this->method('POST', $data);
		return $this;
	}
	/**
	 * 直接post方式请求，不使用array
	 * @param $data
	 * @return $this
	 * @deprecated 2018-04-25
	 */
	public function postF($data = null){
		$this->method('POSTF', $data);
		return $this;
	}
	/**
	 * 直接get方式请求
	 * @param array $query
	 * @return $this
	 */
	public function get($query=[]){
		$this->method('GET', null, $query);
		return $this;
	}
	/**
	 * 请求方法，设定请求方式和请求数据
	 * @param string $method
	 * @param array $data
	 * @return $this
	 */
	public function method($method = 'GET', $data = null, $query =[]){
		$this->method =strtoupper($method);
		$this->data =$data;
		$this->query =array_merge($this->query, $query);
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
	public function query($query=[]){
		$this->query =array_merge($this->query, $query);
		return $this;
	}
	public function contentType($contentType ='application/x-www-form-urlencoded'){
		$this->contentType =$contentType;
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
		switch($this->method){
			case 'GET':
				$this->opts[CURLOPT_HTTPGET] = 1;
				break;
			case 'POST':
				$this->opts[CURLOPT_POST] = 1;
				//unset($this->opts[CURLOPT_HTTPGET]);
				break;
			case 'PUT':
			case 'DELETE':
			case 'HEAD':
			case 'OPTIONS':
			default:
				$this->opts[CURLOPT_CUSTOMREQUEST] = strtoupper($this->method);
				//unset($this->opts[CURLOPT_HTTPGET]);
				break;
		}
		if(is_array($this->query) && !empty($this->query)) $this->opts[CURLOPT_URL] .=((isset($this->opts[CURLOPT_URL]) && strpos($this->opts[CURLOPT_URL], '?') !==false) ?'&' :'?') . http_build_query($this->query);
		if(null !==$this->data){
			switch($this->contentType){
				case self::CONTENT_TYPE_TEXT:
				case self::CONTENT_TYPE_HTML:
					$this->opts[CURLOPT_POSTFIELDS] = $this->data;
					break;
				case self::CONTENT_TYPE_FORM:
					$this->opts[CURLOPT_POSTFIELDS] = $this->data;
					break;
				case self::CONTENT_TYPE_JSON:
					$this->opts[CURLOPT_POSTFIELDS] = json_encode($this->data, JSON_UNESCAPED_UNICODE);
					break;
				case self::CONTENT_TYPE_URL:
				default:
					$this->opts[CURLOPT_POSTFIELDS] = http_build_query($this->data);
					break;
			}
			$this->headers[] ='Content-Type:'.$this->contentType;
		}
		if(!empty($this->headers)) $this->opts[CURLOPT_HTTPHEADER] = $this->headers;
		if(strpos(strtolower($this->opts[CURLOPT_URL]), 'https:')===0){
			$this->opts[CURLOPT_SSL_VERIFYPEER] =false;
			$this->opts[CURLOPT_SSL_VERIFYHOST] =1;
		}
		$this->handle = curl_init();
		@curl_setopt_array($this->handle, $this->opts);
		if(0<$this->logLevel){
			$app =\nx\app::$instance;
			$app->log('curl: '.$this->opts[CURLOPT_URL]);
			$app->log('    - method:'.$this->method);
			if(isset($this->opts[CURLOPT_HTTPHEADER])) $app->log('    - header:'.json_encode($this->opts[CURLOPT_HTTPHEADER], JSON_UNESCAPED_UNICODE));
			if(isset($this->opts[CURLOPT_POSTFIELDS])) $app->log('    - body:'.($this->opts[CURLOPT_POSTFIELDS] ?? ''));
			$start =microtime(true);
			$this->response = curl_exec($this->handle);
			$this->status =$this->info(CURLINFO_HTTP_CODE);
			if(1<$this->logLevel){
				$app->log(sprintf('    - time: %0.3fs', microtime(true)-$start));
				$app->log('    - response status:'.$this->status);
				if(2<$this->logLevel){
					if(3<$this->logLevel && empty($this->response)) $app->log('    - err:'.curl_error($this->handle));
					$app->log('    - response body:'.$this->RW());
				}
			}
		} else{
			$this->response = curl_exec($this->handle);
			$this->status =$this->info(CURLINFO_HTTP_CODE);
		}
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