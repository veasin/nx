<?php
/**
 * Class swagger
 *
 * echo (new swagger('bootstrap.php'));
 *
 */
namespace nx;

class swagger{
	private static $status_code = [//请求已被接受，需要继续处理
		100 => "Continue",
		101 => "Switching Protocols",
		102 => "Processing",
		//请求已成功被服务器接收、理解、并接受
		200 => "OK",
		201 => "Created",//POST PUT PATCH 成功  新的资源已经依据请求的需要而建立
		202 => "Accepted",//异步已添加到队列
		203 => "Non-Authoritative Information",//
		204 => "No Content",//DELETE 成功 禁止包含任何消息体
		205 => "Reset Content",//禁止包含任何消息体
		206 => "Partial Content",//已经成功处理了部分 GET 请求
		207 => "Multi-Status",//可能依照之前子请求数量的不同，包含一系列独立的响应代码
		//需要客户端采取进一步的操作才能完成请求
		300 => "Multiple Choices",
		301 => "Moved Permanently",//被请求的资源已永久移动到新位置
		302 => "Found",//请求的资源临时从不同的 URI响应请求 临时
		303 => "See Other",//对应当前请求的响应可以在另一个 URI 上被找到，而且客户端应当采用 GET 的方式访问那个资源
		304 => "Not Modified",//禁止包含消息体
		305 => "Use Proxy",//被请求的资源必须通过指定的代理才能被访问
		306 => "Switch Proxy",//废弃
		307 => "Temporary Redirect",//请求的资源临时从不同的URI 响应请求
		//客户端看起来可能发生了错误，妨碍了服务器的处理
		400 => "Bad Request",//POST PUT PATCH 无效操作 结果幂等 请求参数有误
		401 => "Unauthorized",//无权限 令牌 用户名 密码错误
		402 => "Payment Required",//需付费
		403 => "Forbidden",//用户得到授权 但禁止访问
		404 => "Not Found", //不存在
		405 => "Method Not Allowed", //方法不被允许
		406 => "Not Acceptable",//请求格式无效
		407 => "Proxy Authentication Required",//与401响应类似，只不过客户端必须在代理服务器上进行身份验证
		408 => "Request Timeout",//请求超时
		409 => "Conflict",//指令冲突
		410 => "Gone",//永久删除
		411 => "Length Required",//服务器拒绝在没有定义 Content-Length 头的情况下接受请求
		412 => "Precondition Failed",//服务器在验证在请求的头字段中给出先决条件时，没能满足其中的一个或多个 Token in header
		413 => "Request Entity Too Large",//请求实体过大
		414 => "Request-URI Too Long",//请求地址过长
		415 => "Unsupported Media Type",//不支持的请求格式
		416 => "Requested Range Not Satisfiable",//请求范围超出
		417 => "Expectation Failed",//预期内容错误
		421 => "There are too many connections from your internet address",
		422 => "Unprocessable Entity", //POST PUT PATCH 创建时验证失败 请求格式正确，但是由于含有语义错误
		423 => "Locked", //当前资源被锁定
		424 => "Failed Dependency", //由于之前的某个请求发生的错误，导致当前请求失败，例如 PROPPATCH
		425 => "Unordered Collection",
		426 => "Upgrade Required", //客户端应当切换到TLS/1.0
		429 => "Too Many Requests", //请求数过多
		431 => "Request Header Fields Too Large", //请求头字段过大
		449 => "Retry With", //由微软扩展，代表请求应当在执行完适当的操作后进行重试
		451 => "Unavailable For Legal Reasons", //该请求因法律原因不可用
		//服务器在处理请求的过程中有错误或者异常状态发生
		500 => "Internal Server Error ", //服务器错误 用户无法判断是否成功
		501 => "Not Implemented ",
		502 => "Bad Gateway ",
		503 => "Service Unavailable ",
		504 => "Gateway Timeout ",
		505 => "HTTP Version Not Supported ",
		506 => "Variant Also Negotiates ",
		507 => "Insufficient Storage ",
		509 => "Bandwidth Limit Exceeded ",
		510 => "Not Extended ",
		600 => "Unparseable Response Headers ",//源站没有返回响应头部，只返回实体内容
	];
	private $bootsrap=__FILE__;
	private $paths=[];
	private $tags=[];
	private $info=[];
	private $host='*';
	private $base='';
	private $def =['base'=>'/'];//	 * @param string $base 接口访问地址(http方式)
	private $custom =[];
	/**
	 * swagger constructor.
	 * @param string $bootstrap 入口文件地址
	 * @param array  $custom 自定义处理配置
	 */
	public function __construct($bootstrap, $custom=[]){
		$this->custom =$custom +$this->def;
		$this->bootsrap=$bootstrap;
		$this->base =$this->custom['base'] ?? $this->def['base'];
		$this->host =$this->custom['host'] ?? $_SERVER['HTTP_HOST'];
		$this->parse();
	}
	/**
	 * 解析nx框架代码
	 */
	public function parse(){
		ob_start();
		include($this->bootsrap);
		ob_end_clean();//防止此app进行输出
		/**
		 * @var \nx\app
		 */
		$app=\nx\app::$instance;
		$app->response->status(200);//强制覆盖当前的输出状态
		$class=get_class($app);
		$path=substr($class, 0, strrpos($class, '\\')).'\\controllers\\';

		$rc =[];
		$before =[];

		if(isset($app->buffer['router/route'])){
			$rules=$app->buffer['router/route']['rules'];
			foreach($rules as $rule){
				if(is_array($rule[2])){
					$route=$rule[2];
					$controller=$path.$route[0];
					$exists=class_exists($controller, true);
					if($exists){
						if(!array_key_exists($controller, $rc)) $rc[$controller] =new \ReflectionClass($controller);
						//if(!array_key_exists($controller, $before)){
						//	$rr =$rule;
						//	$rr[0]='*';
						//	$rr[1]='';
						//	$before[$controller] =$this->parseMethod($rc[$controller], 'before', $rr);
						//}
						$method =$this->parseMethod($rc[$controller], $rule[0].$route[1], $rule);
						if(false !==$method){
							//$mset =$method[2];
							//if($before[$controller] ?? false){
							//	$mset['parameters'] =array_merge($before[$controller][2]['parameters'] ??[], $mset['parameters']??[]);
							//	$mset['responses'] =array_merge($before[$controller][2]['responses']??[], $mset['responses']??[]);
							//}
							$this->paths[$method[0]][$method[1]]=$method[2];
						}
					}
				}
			}
		}
	}
	/**
	 * @param $r \ReflectionClass
	 * @param $rule
	 * @return array|bool
	 */
	public function parseMethod($r, $name, $rule=[]){
		$method=$r->hasMethod($name) ?$r->getMethod($name) :false;
		if(false ===$method) return false;
		$route=$rule[2];
		$tag =strtolower($route[0]);
		if(!array_key_exists($tag, $this->tags)) $this->tags[$tag] =['name'=>$tag, 'desc'=>self::parseDocCommont($r->getDocComment())];
		$_route=self::parseRoute(str_replace('((?<i18n>\w{2})/)*', '', $rule[1]));
		$_path=$_route[0];
		$set=[
			'tags'=>[$tag],
			'summary'=>self::parseDocCommont($method->getDocComment()),
			'description'=>'',
			'produces'=>["application/json", "text/plain"],
			'parameters'=>[],//self::makePathParams($_route[1]),
			'responses'=>[],
			'security'=>[],
		];
		switch($rule[0]){
			case 'post':
			case 'put':
			case 'delete':
				$set['consumes']=['application/x-www-form-urlencoded'];
				break;
		}
		$code=self::readLine($method->getFileName(), $method->getStartLine(), $method->getEndLine());
		$_code=self::parseCode($code);
		$set['parameters']=self::makeRequireParams($_code[0], $rule[0], $_route[1]);
		$set['responses']=self::makeStatus($_code[1]);
		if(!array_key_exists($_path, $this->paths)) $this->paths[$_path]=[];

		$customMethod =$this->custom['method'] ?? false;
		if(is_callable($customMethod)) $set =call_user_func($customMethod, $set, $rule, $_path);

		return [$_path, $rule[0], $set];
	}
	public function doc(){
		return [
			'swagger'=>'2.0',
			'info'=>$this->info,
			'host'=>$this->host,
			'basePath'=>$this->base,
			'schemes'=>['http', 'https'],
			'tags'=>$this->tags,
			'paths'=>$this->paths,
			'responses'=>[
				//'error'=>[
				//	//'description'=>'我们自定义的消息返回格式',
				//	'schema'=>[
				//		'properties'=>[
				//			'err'=>[
				//				'type'=>'integer',
				//				'description'=>'错误编号',
				//			],
				//			//'data'=>[
				//			//	'type'=>'object',
				//			//],
				//			'msg'=>[
				//				'type'=>'string',
				//				'description'=>'错误原因',
				//			],
				//		],
				//	],
				//]
			]
		];
	}
	public function __toString(){
		return json_encode($this->doc(), JSON_UNESCAPED_UNICODE);
	}
	/**
	 * 解析注释文字
	 * @param $doc
	 * @return string
	 */
	static public function parseDocCommont($doc){
		$lines=explode("\n", $doc);
		$r=[];
		foreach($lines as $line){
			$l=trim(trim(trim($line), '/*'));
			if(strlen($l)) $r[]=$l;
		}
		return implode("\n", $r);
	}
	/**
	 * 解析代码中参数和返回状态
	 * @param $code
	 * @return array
	 */
	static public function parseCode($code){
		$params=[];
		preg_match_all('#request->(?<method>get|post|input|arg|params|header)\([\'"](?<name>[^,]+)[\'"](,\s*(?<default>[^,;)]+))?(,\s*[\'"](?<filter>[^,;)]+)[\'"])?(,\s*[\'"](?<pattern>[^,;)]+)[\'"])?\)([,;]\h*//\h*(?<comment>.*))?#', $code, $params, PREG_SET_ORDER);
		$num2=preg_match_all('#(swagger|S):(?<method>get|post|input|arg|params|header)\|(?<name>[^,\|\n]+)(,\s*(?<default>[^,;\|)]+))?(,\s*(?<filter>[^,;\|)]+))?(,\s*[\'"](?<pattern>[^,;\|)]+)[\'"])?\|?(?<comment>[^\n]*)?#', $code, $params2, PREG_SET_ORDER);
		if($num2 >0) $params =array_merge($params, $params2);

		$status=[];
		preg_match_all('#this->[status|i18nStatus]+\((?<code>[1-9]+\d*)(,\s*[\'"](?<info>[^,;)]+)[\'"])?\)([,;]\s*//\h*(?<comment>.*))?#', $code, $status, PREG_SET_ORDER);
		$status2=[];
		$num=preg_match_all('#this->response->status\(\s*(?<code>\d+)(,\s*[\'"](?<info>[^,;)]+)[\'"])?\)([,;]\s*//\h*(?<comment>.*))?#', $code, $status2, PREG_SET_ORDER);
		if($num >0) $status =array_merge($status, $status2);
		return [$params, $status];
	}
	/**
	 * 生成swagger状态结构
	 * @param $params
	 * @return array
	 */
	static public function makeStatus($params){
		$r=[];
		if(empty($params)) return $r;
		foreach($params as $status){
			$desc =self::$status_code[(int)$status['code']] ?? '';
			if(isset($status['info']) && ''!==$status['info']) $desc .=' ('.$status['info'].') ';
			if(isset($status['comment']) && ''!==$status['comment']) $desc .=' ['.$status['comment'].']';
			$r[(int)$status['code']]=['description'=>$desc];
		}
		return $r;
	}
	/**
	 * 生成swagger请求参数结构
	 * @param        $params
	 * @param string $requireMethod
	 * @param array  $path
	 * @return array
	 */
	static public function makeRequireParams($params, $requireMethod='', $path=[]){
		$r=[];
		if(count($path)){
			foreach($path[0] as $idx=>$name){
				$r[$name]=[
					'name'=>$name,
					'in'=>'path',
					'description'=>'',
					'required'=>true,
					'type'=>'string',
					//'pattern'=>$path[1][$idx],
				];
				if(isset($path[1]) && isset($path[1][$idx])){
					$r[$name]['pattern'] =$path[1][$idx];
					if(isset($r[$name]['pattern'])) $r[$name]['description']='格式#'.$path[1][$idx].'# '.$r[$name]['description'];
				}

			}
		}
		foreach($params as $param){
			if(isset($r[$param['name']])){//在path中已经解析出来了
				$r[$param['name']]['description'] =$param['comment'] ?? '';
				if(isset($r[$param['name']]['pattern'])) $r[$param['name']]['description'] ='格式#'.$r[$param['name']]['pattern'].'# '.$r[$param['name']]['description'];
			} else {
				switch($param['method']){
					case 'params':
						$in ='path';
						break;
					case 'header':
						$in ='header';
						break;
					case 'arg':
						$in =$requireMethod;
						break;
					case 'get':
						$in ='query';
						break;
					case 'post':
					case 'input':
					case 'put':
					case 'delete':
						$in='formData';
						break;
					default:
						$in =$param['method'];
						break;
				}
				$r[$param['name']]=[
					'name'=>$param['name'],
					'in'=>$in,
					'description'=>$param['comment'] ?? '',
					'required'=>($param['method'] ==='path'),
					'type'=>'string',
				];
				if(isset($param['pattern']) && ''!==$param['pattern']) $r[$param['name']]['pattern'] =$param['pattern'];
				switch($param['filter'] ?? ''){
					case 'i':
					case 'int':
					case 'integer':
						$r[$param['name']]['type'] ='int32';
						break;
					case 'f':
					case 'float':
						$r[$param['name']]['type'] ='float';
						$r[$param['name']]['pattern'] ='^[.0-9]+$';
						break;
					case 'n':
					case 'num':
						$r[$param['name']]['type'] ='int64';
						$r[$param['name']]['pattern'] ='^\d+$';
						break;
					case 'a':
					case 'arr':
					case 'array':
						break;
					case 'pcre':
					case 'preg':
						break;
					case 'b':
					case 'bool':
					case 'boolean':
						break;
					case 'w':
					case 'word':
						break;
					case 's':
					case 'str':
					case 'string':
						break;
					case 'base64':
						break;
				}
				if(isset($r[$param['name']]['pattern'])) $r[$param['name']]['description'] ='格式#'.$r[$param['name']]['pattern'].'# '.$r[$param['name']]['description'];
				if(isset($param['default']) && ''!==$param['default']) $r[$param['name']]['description'] .=" 默认值为 ".$param['default'];
			}
		}

		return array_values($r);
	}
	/**
	 * 生成swagger路径参数结构
	 * @param $params
	 * @return array
	 */
	static public function makePathParams($params){
		$r=[];
		if(empty($params)) return $r;
		foreach($params[0] as $idx=>$name){
			//if('uid'===$name) continue;
			$r[]=[
				'name'=>$name,
				'in'=>'path',
				'description'=>'格式为 '.$params[1][$idx],
				'required'=>true,
				'type'=>'string',
			];
		}
		return $r;
	}
	/**
	 * 解析路由规则 nx\trait\router\route
	 * @param $route
	 * @return array
	 */
	static public function parseRoute($route){
		if('' === $route) return ['/', []];
		//if('$' ===substr($route, -1, 1)) $route =substr($route, 0, strlen($route ) -1);

		if($route[0] ==='$' || $route[0] ==='^'){//以$开头
			$route=substr($route, 1);
			$num=preg_match_all('#\(\?[P]?(\<([\w\d]+)\>)?([^\)]+)\)#', $route, $params);
			if($num > 0){
				array_shift($params);
				array_shift($params);
				$route=preg_replace('#\(\?[P]?(\<([\w\d]+)\>)?[^\)]+\)#', '{$2}', $route);
			}
			return ["/".$route, $params];
		} elseif(preg_match_all('#([d|w]?)\:([a-zA-Z0-9_]+)#',  $route, $params)>0){
			array_shift($params);
			array_shift($params);
			$route=preg_replace('#([d|w]?)\:([a-zA-Z0-9_]+)#', '{$2}', $route);
			return ["/".$route, $params];
		} else return ["/".$route, []];
	}
	/**
	 * 读取文件段落
	 * @param $file
	 * @param $start
	 * @param $end
	 * @return string
	 */
	static public function readLine($file, $start, $end){
		$f=new \SplFileObject($file, 'r');
		$f->seek($start);
		$str=[];
		for($i=0; $i < ($end - $start); $i++){
			$str[]=trim($f->current());
			$f->next();
		}
		return implode("\n", $str);
	}
}
