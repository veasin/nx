<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/18 018
 * Time: 13:40
 */
namespace nx\mvc\traits;

trait controller{
	use callApp;
	static public $instance=null;
	/**
	 * @var \nx\app
	 */
	public $app;
	/**
	 * @var array
	 */
	public $route=[];
	/**
	 * @var \nx\request
	 */
	public $request=null;
	/**
	 * @var \nx\response
	 */
	public $response=null;
	public function __construct($route, $app){
		$this->app= &$app;
		$this->route=$route;
		static::$instance=$this;
		//init use trait
		$this->initTraits();
		//load from app
		if(is_null($this->response)) $this->response=&$this->app->response;
		if(is_null($this->request)) $this->request=&$this->app->request;
	}
	/**
	 * @param            $name
	 * @param bool|false $hook
	 * @param bool|false $all
	 * @return bool
	 */
	public function exec($name, $hook=false, $all=false){
		if($hook){
			$found=false;
			$methods=$all ?['before', 'before'.$name, $this->request->method().$name, 'on'.$name, 'after'.$name, 'after'] :['before', $this->request->method(), 'on', 'after'];
			$r=false;
			foreach($methods as $_fun){
				if(method_exists($this, $_fun)){
					$found=true;
					$r=call_user_func_array([$this, $_fun], $this->route[2]);
					if($r !== null) break;
				}
			}
			if($found === false) return $this->no_found($name);
			return $r;
		}elseif(method_exists($this, $name)) return call_user_func_array([$this, $name], $this->route[2]);
	}
}