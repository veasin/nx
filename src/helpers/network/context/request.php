<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/12 012
 * Time: 09:13
 */
declare(strict_types=1);
namespace nx\helpers\network\context;

use nx\helpers\network\type;

class request{
	private $uri='';
	private $http=[];
	private $query=[];
	private $body='';
	private $contentType=null;
	private $headers=[];
	public function __construct(string $uri, string $method='GET'){
		$this->uri=$uri;
		$this->http['method']=$method;
	}
	/**
	 * 发送请求
	 * @return \nx\network\context\response
	 */
	public function send($body=null, string $contentType=null):response{
		if(null !==$body) $this->body=$body;
		if(null !==$contentType) $this->contentType =$contentType;

		$this->http['method']=strtoupper($this->http['method']);
		//merge query
		if(is_array($this->query) && !empty($this->query)){
			$this->uri .=(($this->uri && strpos($this->uri, '?') !==false) ?'&' :'?') . http_build_query($this->query);
		}
		//set content type
		//todo body
		$body ='';
		switch($this->http['method']){
			case 'POST':
			case 'PUT':
			case 'PATCH':
				$this->headers['content-type'] =type::ABBR[$this->contentType] ?? $this->contentType ?? type::WWW_FORM;
				switch($this->headers['content-type']){
					case type::JSON:
						$body =json_encode($this->body, JSON_UNESCAPED_UNICODE);
						break;
					case type::WWW_FORM:
						if(is_array($this->body)){
							$body =http_build_query($this->body);
						} elseif(null !==$this->body) $body =(string)$this->body;
						break;
					case type::FORM_DATA:
						if(is_array($this->body)){
							$r =[];
							$boundary ='--nx--'.md5(mt_rand() . microtime());
							foreach($this->body as $key=>$value){//build multipart/form-data
								$str ="--{$boundary}\nContent-Disposition: form-data; name=\"{$key}\"";
								//https://www.w3.org/Protocols/rfc1341/5_Content-Transfer-Encoding.html
								//Content-Transfer-Encoding := "BASE64" / "QUOTED-PRINTABLE" /
								//                             "8BIT"   / "7BIT" /  <1000byte
								//                             "BINARY" / x-token
								switch(gettype($value)){
									case 'string':
										if($value[0]='@'){
											$file =substr($value, 1);
											if(
												false !==realpath($file)
												&& is_file($file)
												&& is_readable($file)
											){
												$type =\mime_content_type($file);
												if(empty($type)) $type ="application/octet-stream";
												$name =basename($file);
												$str .="; filename=\"{$name}\"";
												$str .="\nContent-Type: {$type}";
												$str .="\nContent-Transfer-Encoding: binary";
												$value =file_get_contents($file);
												$str .="\n\n{$value}";
												$str .="\n--{$boundary}--";
											} else $str .="\n\n{$value}";
										} else $str .="\n\n{$value}";
										break;
									case 'array':
										if(array_key_exists('file', $value)
											//&& array_key_exists('type', $value)
											//&& array_key_exists('encode', $value)
											&& array_key_exists('content', $value)
										){
											$file =$value['file'] ?? 'unknow.bin';
											$type =$value['type'] ?? 'application/octet-stream';
											$encode =$value['encode'] ?? 'binary';
											$str .="; filename=\"{$file}\"";
											$str .="\nContent-Type: {$type}";
											$str .="\nContent-Transfer-Encoding: {$encode}";
											$value =$value['content'] ??'';
											$str .="\n\n{$value}";
											$str .="\n--{$boundary}--";
										} else $str .="\n\n{$value}";
										break;
									default:
										$str .="\n\n{$value}";
										break;
								}
								$r[] =$str;
							}
							$body =implode("\n", $r);
							$this->headers['content-type'] .="; boundary={$boundary}";
						} elseif(null !==$this->body) $body =(string)$this->body;
						break;
					default:
						if(null !==$this->body) $body =(string)$this->body;
						break;
				}
				break;
		}
		//echo $body;
		$this->http['content'] =$body;
		//set header
		$headers =[];
		foreach($this->headers as $key=>$content){
			$headers[] =ucwords($key, '-').': '.$content;
		}
		$this->http['header'] =$headers;
		//include file
		//todo catch error
		$context=stream_context_create(['http'=>$this->http]);
		$stream=fopen($this->uri, 'r', false, $context);
		return new response($stream);
	}
	/**
	 * 覆盖设置header中的某个字段，如值为null 移除此字段
	 * @param string      $name
	 * @param string|null $value
	 * @return \nx\network\context\request
	 */
	public function header(string $name, string $value=null):request{
		$name =strtolower($name);
		if(null ===$value){
			unset($this->headers[$name]);
		} else $this->headers[$name] =$value;
		return $this;
	}
	/**
	 * 同事设置多个header ['Connection: close',...] 或 ['Connection'=>'close',...]
	 * @param array $headers
	 * @return \nx\network\context\request
	 */
	public function headers(array $headers=[]):request{
		foreach($headers as $key =>$header){
			if(is_numeric($key)){
				list($key, $value) =explode(':', $header, 2);
				$header =$value;
			}
			if(null ===$header){
				unset($this->headers[strtolower($key)]);
			} else $this->headers[strtolower($key)] =trim($header);
		}
		return $this;
	}
	/**
	 * 修改设置，如果值为null从设置中移除，否则覆盖设置
	 * @param string $key
	 * @param        $value
	 */
	private function nullOrSet(string $key, $value){
		if(null === $value){
			unset($this->http[$key]);
		}else $this->http[$key]=$value;
	}
	/**
	 * 远程服务器支持的 GET，POST 或其它 HTTP 方法。
	 * 默认值是 GET。
	 * @param string $method
	 * @return \nx\network\context\request
	 */
	public function method(string $method='get'):request{
		$this->http['method']=$method;
		return $this;
	}
	/**
	 * 请求期间发送的额外 header 。在此选项的值将覆盖其他值 （诸如 User-agent:， Host: 和 Authentication:）。
	 * @param string|null $header
	 * @return \nx\network\context\request
	 */
	public function headerString(string $header=null):request{
		$this->nullOrSet('header', $header);
		return $this;
	}
	/**
	 * 要发送的 header User-Agent: 的值。如果在上面的 header context 选项中没有指定 user-agent，此值将被使用。
	 * 默认使用 php.ini 中设置的 user_agent。
	 * @param string|null $user_agent
	 * @return \nx\network\context\request
	 */
	public function userAgent(string $user_agent=null):request{
		$this->nullOrSet('user_agent', $user_agent);
		return $this;
	}
	/**
	 * 覆盖当前请求的query参数
	 * @param array $query
	 * @return \nx\network\context\request
	 */
	public function query(array $query=[]):request{
		$this->query=$query;
		return $this;
	}
	/**
	 * 在 header 后面要发送的额外数据。通常使用POST或PUT请求。
	 * @param string $body
	 * @param string $contentType
	 * @return \nx\network\context\request
	 */
	public function body($body='', string $contentType=null):request{
		$this->body=$body;
		$this->contentType =$contentType;
		return $this;
	}
	/**
	 * 覆盖设定contentType类型，支持简化写法 json=>application/json
	 * @param string $contentType
	 * @return \nx\network\context\request
	 */
	public function contentType(string $contentType=type::WWW_FORM):request{
		$this->contentType=$contentType;
		return $this;
	}
	/**
	 * URI 指定的代理服务器的地址。(e.g. tcp://proxy.example.com:5100).
	 * @param string|null $proxy
	 * @return \nx\network\context\request
	 */
	public function proxy(string $proxy=null):request{
		$this->nullOrSet('proxy', $proxy);
		return $this;
	}
	/**
	 * 跟随重定向的最大次数。值为 1 或更少则意味不跟随重定向。
	 * 默认值是 20。
	 * @param bool $no
	 * @return \nx\network\context\request
	 */
	public function NoRedirect(bool $no=false):request{
		$this->nullOrSet('max_redirects', $no ?1 :null);
		return $this;
	}
	/**
	 * HTTP 协议版本。
	 * 默认值是 1.0。
	 * @param float|null $version
	 * @return \nx\network\context\request
	 */
	public function protocolVersion(float $version=null):request{
		$this->nullOrSet('protocol_version', $version);
		return $this;
	}
	/**
	 * 读取超时时间，单位为秒（s），用 float 指定(e.g. 10.5)。
	 * 默认使用 php.ini 中设置的 default_socket_timeout。
	 * @param float|null $second
	 * @return \nx\network\context\request
	 */
	public function timeout(float $second=null):request{
		$this->nullOrSet('timeout', $second);
		return $this;
	}
	/**
	 * 即使是故障状态码依然获取内容。
	 * 默认值为 FALSE.
	 * @param bool $no
	 * @return \nx\network\context\request
	 */
	public function ignoreStatusCode(bool $no=false):request{
		$this->http['ignore_errors']=!$no;
		return $this;
	}
}