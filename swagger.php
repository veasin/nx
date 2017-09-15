<?php
/**
 * Class swagger
 *
 * echo (new parse2Swagger('bootstrap.php'));
 *
 */

class swagger{
	private $bootsrap=__FILE__;
	private $paths=[];
	private $tags=[];
	private $info=[];
	private $host='*';
	private $base='';
	public function __construct($bootstrap){
		$this->bootsrap=$bootstrap;
		$this->host =$_SERVER['HTTP_HOST'];
		$this->base ='/'.$bootstrap;
		$this->parse();
	}
	public function parse(){
		include($this->bootsrap);
		/**
		 * @var \nx\app
		 */
		$app=\nx\app::$instance;
		$class=get_class($app);
		$path=substr($class, 0, strrpos($class, '\\')).'\\controllers\\';
		if(isset($app->buffer['router/route'])){
			$rules=$app->buffer['router/route']['rules'];
			foreach($rules as $rule){
				if(is_array($rule[2])){
					$route=$rule[2];
					$controller=$path.$route[0];
					$exists=class_exists($controller, true);
					if($exists){
						$r=new ReflectionClass($controller);
						try{
							$method=$r->getMethod($rule[0].$route[1]);
						} catch(ReflectionException $e){
							$method =false;
						}
						if($method !==false){
							$tag =strtolower($route[0]);
							if(!array_key_exists($tag, $this->tags)) $this->tags[$tag] =['name'=>$tag, 'desc'=>self::parseDocCommont($r->getDocComment())];
							$_route=self::parseRoute(str_replace('((?<i18n>\w{2})/)*', '', $rule[1]));
							$_path=$_route[0];
							$set=[
								'tags'=>[$tag],
								'summary'=>self::parseDocCommont($method->getDocComment()),
								'description'=>'',
								'produces'=>["application/json"],
								'parameters'=>self::makePathParams($_route[1]),
								'responses'=>[
									//'default'=>[
									//	'$ref'=>'#/responses/error',
									//]
								],
								'security'=>[],
							];
							switch($rule[0]){
								case 'post':
								case 'put':
								case 'delete':
									$set['consumes']=['application/x-www-form-urlencoded'];
									break;
							}
							if(strpos($_path, '{uid}') !==false || strpos($_path, '{pid}') !==false || strpos($_path, '{admin_id}') !==false){
								$set['security']=[
									[
										"Token"=>['read:token'],
									]
								];

							}
							//$code ='';
							$code=self::readLine($method->getFileName(), $method->getStartLine(), $method->getEndLine());
							$_code=self::parseCode($code);
							//var_dump($rule[1], $r->getDocComment(), $method->getDocComment(), $method->getParameters(), $method->getFileName());
							$set['parameters']=array_merge($set['parameters'], self::makeRequireParams($_code[0], $rule[0]));
							//$set['responses']=self::makeStatus($_code[1]);
							if(!array_key_exists($_path, $this->paths)) $this->paths[$_path]=[];
							$this->paths[$_path][$rule[0]]=$set;
						}
						//die();
					}
				}
			}
			//var_dump($rules);
		}
	}
	public function doc(){
		return [
			'swagger'=>'2.0',
			'info'=>$this->info,
			'host'=>$this->host,
			'basePath'=>$this->base,
			'schemes'=>['http'],
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
	static public function parseDocCommont($doc){
		$lines=explode("\n", $doc);
		$r=[];
		foreach($lines as $line){
			$l=trim(trim(trim($line), '/*'));
			if(strlen($l)) $r[]=$l;
		}
		return implode("\n", $r);
	}
	static public function parseCode($code){
		$params=[];
		$num=preg_match_all('#request->(get|post|input|arg)\(([^,]+),([^,;)]+)(,[^)]+)?\)#', $code, $params);
		if($num > 0){
			array_shift($params);
			//var_dump($params);
		}
		$status=[];
		$num=preg_match_all('#this->[status|i18nStatus]+\(([1-9]+\d*),(.+)\)#', $code, $status);
		if($num > 0) array_shift($status);
		return [$params, $status];
	}
	static public function makeStatus($params){
		$r=[];
		if(empty($params)) return $r;
		foreach($params[0] as $idx=>$num){
			$r[(int)$num]=[
				'description'=>trim(trim(trim($params[1][$idx]), '\'"')),
			];
		}
		return $r;
	}
	static public function makeRequireParams($params, $requireMethod=''){
		$r=[];
		if(empty($params)) return $r;
		foreach($params[0] as $idx=>$method){
			$name =trim(trim(trim($params[1][$idx]), '\'"'));
			$default =trim($params[2][$idx], " \"'");
			$in =$method;
			switch($method){
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
			}
			$r[]=[
				'name'=>$name,
				'in'=>$in,
				'description'=>'default is '.$default.'',
				'required'=>($method ==='path'),
				'type'=>'string',
				'format'=>trim(trim(trim(trim($params[3][$idx], ',')), '\'"')),
			];
		}
		return $r;
	}
	static public function makePathParams($params){
		$r=[];
		if(empty($params)) return $r;
		foreach($params[0] as $idx=>$name){
			//if('uid'===$name) continue;
			$r[]=[
				'name'=>$name,
				'in'=>'path',
				'description'=>'',
				'required'=>true,
				'type'=>'string',
				'format'=>$params[1][$idx],
			];
		}
		return $r;
	}
	static public function parseRoute($route){
		if('' === $route) return ['', []];
		if($route[0] !== '$') return ["/".$route, []];
		$route=substr($route, 1);
		$num=preg_match_all('#\(\?[P]?(\<([\w\d]+)\>)?([^\)]+)\)#', $route, $params);
		if($num > 0){
			array_shift($params);
			array_shift($params);
			$route=preg_replace('#\(\?[P]?(\<([\w\d]+)\>)?[^\)]+\)#', '{$2}', $route);
		}
		return ["/".$route, $params];
	}
	static public function readLine($file, $start, $end){
		$f=new SplFileObject($file, 'r');
		$f->seek($start);
		$str=[];
		for($i=0; $i < ($end - $start); $i++){
			$str[]=trim($f->current());
			$f->next();
		}
		return implode("\n", $str);
	}
}

