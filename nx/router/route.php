<?php
namespace nx\router;

/**
 * Class route
 * @trait   app
 * @package nx\router
 */
/*
 *
[
	'actions'=>[//固定名称
		'动作组名'=>[//actions
			['user','check'],
			['user','check','参数'],
			['\api\user','check','参数', '参数2', '参数3'],
			[$this, 'main', '参数', '参数2'],
			[null, function(){},'参数', '参数2'],//null => 绑定对象
		]
	],
	['method', 'uri', ['more', 'apps'], '动作组名'], //应用推荐
]
$app->run()
$app->router() =>xxx
$app->control(xxx);
-------------------------
$app->run();
$app->control();
function control(){
  $next =$app->router->next()
}
*/
class route{
	/**
	 * @var array[] 路由规则
	 */
	private $rules=[];
	/**
	 * @var array[] 动作组名
	 */
	private $actions=[];
	private $uri='';
	private $method='';
	/**
	 * @var \nx\app
	 */
	private $app=null;
	/**
	 * @var array[]
	 */
	private $calls=[];
	public function __construct($setup=[]){
		$this->app=\nx\app::$instance;
		$this->rules=$setup['rules'] ?? [];
		$this->actions=$setup['actions'] ?? [];
		$this->uri=$setup['uri'] ?? trim((isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) ?$_SERVER['PATH_INFO'] :$_SERVER['QUERY_STRING'], '/');
		$this->method=$setup['method'] ?? $this->app->in['method'] ?? 'unknown';
	}
	public function next(){
		foreach($this->rules as $rule){//0 method 1 uri 2 action[controller, action, args] 3 action...
			if(empty($rule[2])) continue;//如果没定义处理方法，那么继续
			$method=array_shift($rule);
			if(!($this->method === $method || '*' === $method)) continue;//如果没有匹配直接继续下一个
			$is_match=0;
			$params=[];
			$uri=array_shift($rule);
			if($this->uri === $uri || '*' === $uri){//如果网址和规则相同
				$is_match=1;
			}elseif(preg_match_all('#([d|w]?)\:([a-zA-Z0-9_]*)#', $uri) > 0){
				$end=substr($uri, -1);
				$pattern='#^'.preg_replace_callback('#([d|w]?)\:([a-zA-Z0-9_]*)#', function($matches){
						$m=['d'=>'\d+', 'w'=>'\w+', ''=>'[^/]+'];
						return '('.('' != $matches[2] ?'?P<'.$matches[2].'>' :'').$m[$matches[1]].')';
					}, $uri).($end == '+' ?'#' :'$#');
				$is_match=preg_match($pattern, $this->uri, $params);
			}
			if($is_match){//如果匹配规则成功
				$_params=[];
				if(count($params) > 0){//从路由中拿出参数并去掉命名
					ksort($params);
					for($i=1, $max=count($params); $i < $max; $i++){
						if(isset($params[$i])) $_params[]=$params[$i];else break;
					}
				}
				$this->calls=[];
				foreach($rule as $call){
					if(is_string($call)){
						if(array_key_exists($call, $this->actions)){
							foreach($this->actions[$call] as $_call){
								$this->calls[]=$_call;
								yield [$_call[0], $_call[1]]=>array_key_exists(2, $_call) ?array_merge($_params, $_call[2] ?? []) :$_params;
							}
						}
					}else{
						$this->calls[]=$call;
						yield [$call[0], $call[1]]=>array_key_exists(2, $call) ?array_merge($_params, $call[2] ?? []) :$_params;
					}
				}
			}
		}
		return null;
	}
}

