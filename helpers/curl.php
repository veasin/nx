<?php
namespace nx\helpers;

class curl{
	private $handle = null;
	private $response = null;
	public $opts = [];
	private $headers=[];
	public static function factory($url, $method = 'GET', $args = []){
		return new self($url, $method, $args);
	}
	public function __construct($url, $method = 'GET', $args = []){
		$this->url($url);
		$this->method($method, $args);
		return $this;
	}
	public function __destruct(){
		if(!is_null($this->handle)) curl_close($this->handle);
	}
	public function file($Filename, $Minetype = null, $Postname = null){
		return curl_file_create($Filename, $Minetype, $Postname);
	}
	public function url($url){
		$this->opts[CURLOPT_URL] = $url;
		return $this;
	}
	public function post($Data){
		$this->method('POST', $Data);
		return $this;
	}
	public function postF($Data){
		$this->method('POSTF', $Data);
		return $this;
	}
	public function get(){
		$this->method('GET');
		return $this;
	}
	public function method($method = 'GET', $data = []){
		switch(strtoupper($method)){
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
	public function httpHeader($header){
		$this->opts[CURLOPT_HTTPHEADER] = $header;
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
		//$this->opts[CURLOPT_HEADER] = $has;
		return $this;
	}
	public function nobody($has = 1){
		$this->opts[CURLOPT_NOBODY] = $has;
		return $this;
	}
	public function timeout($MS = 0){
		if($MS >0) $this->opts[CURLOPT_TIMEOUT] = $MS;
		else unset($this->opts[CURLOPT_TIMEOUT]);
		return $this;
	}
	public function agent($User){
		$this->opts[CURLOPT_USERAGENT] = $User;
		return $this;
	}
	public function user($username, $password=''){
		$this->opts[CURLOPT_USERPWD] =$username.':'.$password;
		return $this;
	}
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
	public function range($d1, $d2){
		$this->opts[CURLOPT_RANGE] = $d1.'-'. $d2;
		return $this;
	}
	public function set($key, $value){
		$this->opts[$key] = $value;
		return $this;
	}
	public function RW(){
		return is_string($this->response) ?$this->response :'';
	}
	public function rwCookie(){
		preg_match_all('|Set-Cookie: (.*);|U', $this->response, $matches);
		return $matches[1];
	}
	public function exec($ReturnTransfer = true){
		if($ReturnTransfer){
			$this->opts[CURLOPT_BINARYTRANSFER] = 1;
			$this->opts[CURLOPT_RETURNTRANSFER] = 1;
		}
		$this->handle = curl_init();
		if(!empty($this->headers)) $this->opts[CURLOPT_HTTPHEADER] = $this->headers;
		@curl_setopt_array($this->handle, $this->opts);
		\nx\app::$instance->log('curl->exec('.$this->opts[CURLOPT_URL].' , '.(
			isset($this->opts[CURLOPT_POSTFIELDS])
				?json_encode($this->opts[CURLOPT_POSTFIELDS], JSON_UNESCAPED_UNICODE)
				:'[]'
			).')');
		$start =microtime(true);
		$this->response = curl_exec($this->handle);
		\nx\app::$instance->log(' - time:'.(microtime(true)-$start).'ms');
		return $this;
	}
	/**
	 * @param string $Opt //"url" "content_type" "http_code" "header_size" "request_size" "filetime" "ssl_verify_result" "redirect_count" "total_time" "namelookup_time" "connect_time" "pretransfer_time" "size_upload" "size_download" "speed_download" "speed_upload" "download_content_length" "upload_content_length"  "redirect_time"
	 * @return string
	 */
	public function info($Opt = null){
		if(is_null($Opt)){
			return curl_getinfo($this->handle);
		}
		else return curl_getinfo($this->handle, $Opt);
	}
	public function parse(){
		if(is_null($this->handle)) $this->exec(true);
		$r = $this->RW();
		return json_decode($r, true);
	}
}