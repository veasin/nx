<?php
namespace nx\router;

/**
 * Class route
 * @trait   app
 * @package nx\router
 * @deprecated 2019-02-28
 */
trait route{
	protected function nx_router_route(){
		if(!isset($this->buffer['router/route'])) $this->buffer['router/route']=isset($this->setup['router/route']) ?$this->setup['router/route'] :['rules'=>[]];
	}
	/**
	 * 默认路由方法 循环匹配路由
	 * @return mixed
	 */
	protected function router(){
		if(empty($this->buffer['router/route']['rules'])) return $this->control(404);//如果规则为空那么直接404
		$method=$this->request->method();
		$uri=trim((isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) ?$_SERVER['PATH_INFO'] :$_SERVER['QUERY_STRING'], '/');
		$this->log('route uri: '.$uri);
		$no_match=true;
		foreach($this->buffer['router/route']['rules'] as $route){//0 method 1 route 2 control[controller, action, fake method] 3 args
			if(false === $route[2] && isset($route[3])) $route[2]=$route[3];//兼容旧版本逻辑
			if(empty($route[2])) continue;//如果没定义处理方法，那么继续
			if(!($method === $route[0] || '*' === $route[0])) continue;//如果没有匹配直接继续下一个
			$match_route=false;
			$path=$route[1];
			$un=isset($path[0]) && $path[0] === '!';//规则是否取反
			$i=$un ?1 :0;
			$match_prefix =['$'=>'$', '^'=>''];//设定正则路由识别标识 $ 完全匹配 ^ 只匹配开头
			$params=[];
			if('*' === $path || '404' === $path || '405' === $path || 404 === $path || 405 === $path) $match_route=true;
			elseif($uri === $path){//如果网址和规则相同
				$match_route=true;
				$no_match=false;
			}elseif(isset($path[$i]) && array_key_exists($path[$i], $match_prefix)){//如果网址和规则匹配，默认只从头匹配
				$match_route=preg_match('#^'.substr($path, $i + 1).$match_prefix[$path[$i]].'#', $uri, $params);
				if(0 <$match_route) $no_match=false;
			}elseif(preg_match_all('#([d|w]?)\:([a-zA-Z0-9_]*)#',  $path)>0){
				$end =substr($path, -1);
				$pattern ='#^'.preg_replace_callback('#([d|w]?)\:([a-zA-Z0-9_]*)#',function($matches){
						$m =['d'=>'\d+', 'w'=>'\w+', ''=>'[^/]+'];
						return '('.(''!=$matches[2] ?'?P<'.$matches[2].'>':'').$m[$matches[1]].')';
					}, $path).($end =='+' ?'#' :'$#');
				$match_route=preg_match($pattern, $uri, $params);
				if(0 <$match_route) $no_match=false;
			}
			if($match_route){//如果匹配规则成功
				$this->log(' - match: '.$path);
				$_params=[];
				if(count($params) > 0){//从路由中拿出参数并去掉命名
					ksort($params);
					for($i=1, $max=count($params); $i < $max; $i++){
						if(isset($params[$i])) $_params[]=$params[$i];else break;
					}
				}
				$this->request['params']=$params;//兼容旧逻辑，数字key和名称key同时存在
				$params=!isset($route[3]) ?$_params :array_merge($_params, $route[3]);//重新调整参数顺序，确保路由参数在前
				$result=null;
				if(is_array($route[2])){
					if(isset($route[2][2])) $this->request['method']=$route[2][2];//覆盖method
					$route[2][2]=$params;
					$result=call_user_func_array([$this, 'control'], [$route[2]]);//0 controller 1 action 2 args
					$this->request['method']=$method;//恢复method
				}elseif(is_callable($route[2])){
					if($route[2] instanceof \Closure) $route[2] =$route[2]->bindTo($this);
					$result=call_user_func_array($route[2], $params);
				}
				if(null !== $result) return $result;
			}
		}
		if($no_match) $this->log(' - match: nothing(404)');
	}
	/**
	 * 执行应用
	 * @param array ...$route
	 */
	public function run(...$route){
		return 0 === count($route) ?$this->router() :$this->control(...$route);
	}
	/**
	 * 添加路由规则
	 * @param string         $route   路由规则
	 * @param callable|array $control 回调
	 * @param array          $params  额外供回调使用参数
	 * @param string         $method  请求方法 默认为 *
	 * @param bool           $first   是否添加到第一条
	 * @return $this
	 */
	public function on(string $route, $control, array $params=[], string $method='*', bool $first=false){
		$_control=is_a($control, 'Closure') ?$control->bindTo($this) :$control;
		if($first) array_unshift($this->buffer['router/route']['rules'], [
			$method,
			$route,
			$_control,
			$params,
		]);else $this->buffer['router/route']['rules'][]=[$method, $route, $_control, $params];
		return $this;
	}
	/**
	 * 添加路由规则 get 快捷添加
	 * @param string         $route   路由规则
	 * @param callable|array $control 回调
	 * @param array          $params  额外供回调使用参数
	 * @return $this
	 */
	public function get(string $route, $control, array $params=[]){
		$this->buffer['router/route']['rules'][]=['get', $route, $control, $params];
		return $this;
	}
	/**
	 * 添加路由规则 post 快捷添加
	 * @param string         $route   路由规则
	 * @param callable|array $control 回调
	 * @param array          $params  额外供回调使用参数
	 * @return $this
	 */
	public function post(string $route, $control, array $params=[]){
		$this->buffer['router/route']['rules'][]=['post', $route, $control, $params];
		return $this;
	}
	/**
	 * 添加路由规则 put 快捷添加
	 * @param string         $route   路由规则
	 * @param callable|array $control 回调
	 * @param array          $params  额外供回调使用参数
	 * @return $this
	 */
	public function put(string $route, $control, array $params=[]){
		$this->buffer['router/route']['rules'][]=['put', $route, $control, $params];
		return $this;
	}
	/**
	 * 添加路由规则 delete 快捷添加
	 * @param string         $route   路由规则
	 * @param callable|array $control 回调
	 * @param array          $params  额外供回调使用参数
	 * @return $this
	 */
	public function delete(string $route, $control, array $params=[]){
		$this->buffer['router/route']['rules'][]=['delete', $route, $control, $params];
		return $this;
	}
}