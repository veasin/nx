<?php
namespace nx\router;

trait route{
	private $_router_rules =[];
	public function router(){
		if(empty($this->_router_rules)) $this->control(404);

		$method =strtolower($_SERVER['REQUEST_METHOD']);
		$uri =isset($_SERVER['PATH_INFO']) ?$_SERVER['PATH_INFO'] :$_SERVER['QUERY_STRING'];

		foreach($this->_router_rules as $route){
			//var_dump($route);

			$_match_method =($route['method'] ==$method || $route['method'] =='*');
			//如果没有匹配直接继续下一个
			if(!$_match_method) continue;

			$_match_path =false;
			$path =$route['route'];
			$un =isset($path[0]) && $path[0] =='!';
			$i =$un ?1 :0;
			$params =[];
			$regex = false;


			if($path ==$uri || $path==='*') $_match_path =true;
			elseif($path =='404' || $path =='405'){
				$_match_path =true;
			}elseif(isset($path[$i]) && $path[$i] === '$') {
				$_match_path = preg_match('#^' . substr($path, $i + 1) . '$#', $uri, $params);
			} else{
				$expression = null;
				$j =0;
				while ($c =$n =$path[$i]){
					if($regex ==false){
						$regex =($n ==='[') || ($n ==='(') || ($n ==='.');
						if(!$regex && isset($path[$i+1])){
							$n=$path[$i+1];
							$regex =($n ==='?') || ($n ==='+') || ($n ==='*') || ($n ==='{');
						}
						if($regex) break;
						elseif($c !=='/' && (!isset($uri[$j]) || $c !==$uri[$j])){
							continue 2;
						}

						$j++;
					}
					$i++;
				};
				if($regex){
					$regex = $this->compileRoute($path);
					$_match_path = preg_match($regex, $uri, $params);
				} else $_match_path =true;
			}

			if($_match_path){
				if(isset($params[0])) array_shift($params);
				//$this->match =$route['route'];
				//$this->route =$params;

				$this->request['params'] =$params;

				$result =call_user_func_array($route['callback'] ?$route['callback'] :[$this, 'control'], [$this->request, $this->response, $this]);
				if(!is_null($result)) return $result;
			}
			//var_dump('match', $_match_path, $regex, $params);


		}

	}

	public function on($route, $callback){
		$this->_router_rules[] =['method'=>'*', 'route'=>$route, 'callback'=>$callback];
	}
	public function get($route, $callback){
		$this->_router_rules[] =['method'=>'get', 'route'=>$route, 'callback'=>$callback];
	}
	public function post($route, $callback){
		$this->_router_rules[] =['method'=>'post', 'route'=>$route, 'callback'=>$callback];
	}
	public function put($route, $callback){
		$this->_router_rules[] =['method'=>'put', 'route'=>$route, 'callback'=>$callback];
	}
	public function delete($route, $callback){
		$this->_router_rules[] =['method'=>'delete', 'route'=>$route, 'callback'=>$callback];
	}




}