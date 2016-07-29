<?php
namespace nx\router;

/**
 * Class route
 * @trait app
 * @package nx\router
 */
trait route{
	protected function nx_router_route(){
		if(!isset($this->buffer['router/route'])) $this->buffer['router/route']=isset($this->setup['router/route']) ?$this->setup['router/route'] :['rules'=>[]];
	}
	public function router(){
		if(empty($this->buffer['router/route']['rules'])) return $this->control(404);

		$method =$this->request->method();
		$uri=(isset($_SERVER['PATH_INFO']) && !empty($_SERVER['PATH_INFO'])) ?ltrim($_SERVER['PATH_INFO'], '/') :$_SERVER['QUERY_STRING'];
		$_params =[];
		if(strpos($uri, '?')!==false){
			list($uri, $qs) =explode('?', $uri);
			parse_str($qs, $_params);
		}
		$this->log('route uri: '.$uri);
		foreach($this->buffer['router/route']['rules'] as $route){
			$_match_method=($route[0]==$method || $route[0]=='*');
			//如果没有匹配直接继续下一个
			if(!$_match_method) continue;

			$_match_path=false;
			$path=$route[1];
			$un=isset($path[0]) && $path[0]=='!';
			$i=$un ?1 :0;
			$params=$_params;

			if($path==$uri || $path==='*') $_match_path=true;
			elseif($path=='404' || $path=='405'){
				$_match_path=true;
			}elseif(isset($path[$i]) && $path[$i]==='$'){
				$_match_path=preg_match('#^'.substr($path, $i+1).'$#', $uri, $params);
			}else{
				//自定义格式规则，拟使用klein
			}
			if($_match_path){
				$this->log(' - uri: '.$path.' ['.($_match_path ?'match':'no').']');
				if(isset($params[0])) array_shift($params);
				$this->request['params']=$params;
				if($route[2]===false){
					if(isset($route[3][2])) $this->request['method'] =$route[3][2];
					$result =call_user_func_array([$this, 'control'], [$route[3]]);
				} else $result =call_user_func_array($route[2], [$this->request, $this->response, $this]);
				if(!is_null($result)) return $result;
			}
		}
	}
	public function on($route, $callback, $control=[], $method='*', $first=false){
		$_callback =is_a($callback, 'Closure') ?$callback->bindTo($this) :$callback;
		if($first) array_unshift($this->buffer['router/route']['rules'], [$method, $route, $_callback, $control]);
		else $this->buffer['router/route']['rules'][]=[$method, $route, $_callback, $control];
		return $this;
	}
	public function get($route, $callback, $control=[]){
		$this->buffer['router/route']['rules'][]=['get', $route, $callback, $control];
		return $this;
	}
	public function post($route, $callback, $control=[]){
		$this->buffer['router/route']['rules'][]=['post', $route, $callback, $control];
		return $this;
	}
	public function put($route, $callback, $control=[]){
		$this->buffer['router/route']['rules'][]=['put', $route, $callback, $control];
		return $this;
	}
	public function delete($route, $callback, $control=[]){
		$this->buffer['router/route']['rules'][]=['delete', $route, $callback, $control];
		return $this;
	}
}