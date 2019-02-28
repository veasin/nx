<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/28 028
 * Time: 09:53
 */
namespace nx\router;

trait uri{
	protected function router(){
		$setup =$this->setup['router/uri'];

		$rules=$setup['rules'] ?? [];
		$actions=$setup['actions'] ?? [];
		$uri=$setup['uri'] ?? (isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) ?$_SERVER['PATH_INFO'] :$_SERVER['QUERY_STRING']??'';
		$method=$setup['method'] ?? $this->app->in['method'] ?? 'unknown';

		$this->log("route uri: {$uri}");
		foreach($rules as $rule){//0 method 1 uri 2 action[controller, action, args] 3 action...
			if(empty($rule[2])) continue;//如果没定义处理方法，那么继续
			$_method=array_shift($rule);
			if(!($method === $_method || '*' === $_method)) continue;//如果没有匹配直接继续下一个
			$is_match=0;
			$params=[];
			$_uri=array_shift($rule);
			if($uri === $_uri || '*' === $_uri){//如果网址和规则相同
				$is_match=1;
			}elseif(preg_match_all('#([d|w]?)\:([a-zA-Z0-9_]*)#', $_uri) > 0){
				$end=substr($_uri, -1);
				$pattern='#^'.preg_replace_callback('#([d|w]?)\:([a-zA-Z0-9_]*)#', function($matches){
						$m=['d'=>'\d+', 'w'=>'\w+', ''=>'[^/]+'];
						return '('.('' != $matches[2] ?'?P<'.$matches[2].'>' :'').$m[$matches[1]].')';
					}, $uri).($end == '+' ?'#' :'$#');
				$is_match=preg_match($pattern, $uri, $params);
			}
			if($is_match){//如果匹配规则成功
				$this->log("route match: {$uri}");
				for($i=0, $max=count($params); $i < $max; $i++){
					unset($params[$i]);
				}
				foreach($rule as $call){
					if(is_string($call)){
						if(array_key_exists($call, $actions)){
							foreach($actions[$call] as $_call){
								$this->in['params'] =array_key_exists(2, $_call) ?array_merge($params, $_call[2] ?? []) :$params;
								yield [$_call[0], $_call[1]];
							}
						}
					}else{
						$this->in['params'] =array_key_exists(2, $call) ?array_merge($params, $call[2] ?? []) :$params;
						yield [$call[0], $call[1]];
					}
				}
			}
		}
		return null;
	}
	public function run(...$route){
		$g=$this->router();
		$next=function(...$_args) use (&$next, $g, $route){
			if($g->valid()){
				$call=$g->current();
				$g->next();
				$result=$this->control($call, $next, ...$_args, ...$route);
			}else $result=$args[0] ?? null;
			return $result;
		};
		return $next();
	}
}