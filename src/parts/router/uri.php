<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/28 028
 * Time: 09:53
 */
namespace nx\parts\router;

/**
 * @method runtime(string $string, string $from)
 * @method control(mixed $call, \Closure $next, $param, array $_args, $param1, array $route)
 * @property \nx\helpers\input $in
 */
trait uri{
	protected function router(): ?\Generator{
		['rules'=>$rules,'actions'=>$actions, 'uri'=>$uri, 'method'=>$method]=$this['router/uri'];
		if(null ===$uri) $uri =(!empty($_SERVER['PATH_INFO']) ?$_SERVER['PATH_INFO'] :$_SERVER['QUERY_STRING']??'');
		$simpleMethod =['G'=>'get', 'P'=>'post', 'U'=>'put', 'A'=>'patch', 'D'=>'delete', 'O'=>'options'];
		yield $this->runtime("uri: $uri", 'uri');//默认暂停
		foreach($rules ?? [] as $rule){//0 method 1 uri 2 action[controller, action, args] 3 action...
			$_method=array_shift($rule);
			if(strlen($_method)>1 && ":"===$_method[1]){//简写 G:/xxx
				$_uri =substr($_method, 2);
				$_method =$simpleMethod[$_method[0]] ?? $_method[0];
			} else $_uri=array_shift($rule);

			if(empty($rule)) continue;//如果没定义处理方法，那么继续
			if(!(($method ?? $this->in['method'] ?? 'unknown') === $_method || '*' === $_method)) continue;//如果没有匹配直接继续下一个
			$is_match=0;
			$params=[];
			if($uri === $_uri || '*' === $_uri){//如果网址和规则相同
				$is_match=1;
			}else{
				$__uri =$_uri;
				$_end='+'===$_uri[strlen($_uri)-1];
				$_end_pattern ='$';
				if($_end){
					$_end_pattern ='';
					$__uri =substr($_uri, 0, -1);
				}
				if($_end || preg_match_all('#([d|w]?):(\w*)#', $__uri) > 0){
					$pattern =preg_replace_callback('#([d|w]?):(\w*)#', fn($matches)=>'('.('' !== $matches[2] ?'?P<'.$matches[2].'>' :'').['d'=>'\d+', 'w'=>'\w+', ''=>'[^/]+'][$matches[1]].')', $__uri);
					$is_match=preg_match("#^$pattern$_end_pattern#", $uri, $params);
				}
			}
			if($is_match){//如果匹配规则成功
				$this->runtime("route: $_uri", 'uri');
				for($i=0, $max=count($params); $i < $max; $i++){
					unset($params[$i]);
				}
				foreach($rule as $call){
					if(is_string($call)){
						if(array_key_exists($call, $actions ?? [])) {
							foreach ($actions[$call] as $_call) {
								$this->in['params'] = array_key_exists(2, $_call) ? array_merge($params, $_call[2] ?? []) : $params;
								yield [$_call[0], $_call[1]];
							}
						}elseif(str_contains($call, '::')){
							$this->in['params'] =$params;
							yield explode("::", $call);
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
	public function run(...$route):mixed{
		$g=$this->router();
		$next=function(...$_args) use (&$next, $g, $route){
			$g->next();//因为next本身是作为下一个函数调用的，即需要先知道下一步的call，so，需要先yield一次
			if($g->valid()){
				$call=$g->current();
				$result=$this->control($call, $next, ...$_args, ...$route);
			}else $result=$args[0] ?? null;
			return $result;
		};
		return $next();
	}
}