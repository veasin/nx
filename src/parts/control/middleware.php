<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 10:20
 */
namespace nx\parts\control;

trait middleware{
	protected $cacheControllers=[];
	/**
	 * @param callable|array|null $call
	 * @param mixed               ...$args
	 * @return mixed
	 */
	public function control($call, ...$args){
		if(is_callable($call)){//['\api\user','check'],[$this, 'main'],
			return call_user_func_array($call, $args);
		}elseif(isset($call[1]) && $call[1] instanceof \Closure){//[null, function(){}],
			return call_user_func_array($call[1]->bindTo($call[0] ?? $this), $args);
		}else{
			if(isset($call[0]) && isset($call[1]) && is_string($call[0]) && is_string($call[1])){//['user','check'],
				$pos =strrpos(__CLASS__, '\\');
				$name=substr(__CLASS__, 0, $pos?$pos:strlen(__CLASS__)).'\controllers\\'.$call[0];
				if(!array_key_exists($name, $this->cacheControllers)) $this->cacheControllers[$name] =class_exists($name, true) ?new $name($this) :null;
				if($this->cacheControllers[$name] ?? false){
					$exists =method_exists($this->cacheControllers[$name], $call[1]);
					$this->log("     {$name}->{$call[1]}()".($exists ?'' :' (✗)'));
					return ($exists) ?call_user_func_array([$this->cacheControllers[$name], $call[1]], $args) :null;
				} else {
					$this->log("     {$name} (✗)");
					return null;
				}
			}
		}
	}
}