<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 10:20
 */
namespace nx\parts\control;

/**
 * @method log(string $string)
 * @property-read \nx\helpers\buffer $buffer
 */
trait middleware{
	protected array $cacheControllers=[];
	/**
	 * @param callable|array|null $call
	 * @param mixed               ...$args
	 * @return mixed
	 */
	public function control(callable|array|null $call=null, ...$args){
		if(isset($call[1]) && $call[1] instanceof \Closure){//[null, function(){}],
			return call_user_func_array($call[1]->bindTo($call[0] ?? $this), $args);
		}
		if(isset($call[0], $call[1]) && is_string($call[0]) && is_string($call[1])){//['user','check'],
			if('\\' === $call[0][0]){
				$name=$call[0];
			}else{
				$pos=strrpos(__CLASS__, '\\');
				$name=substr(__CLASS__, 0, $pos ?:strlen(__CLASS__)).'\controllers\\'.$call[0];
			}
			if(!isset($this->buffer['control/middleware'])) $this->buffer['control/middleware']=[];
			if(!array_key_exists($name, $this->buffer['control/middleware'])){
				$this->buffer['control/middleware'][$name]=class_exists($name, true) ?new $name($this) :null;
			}
			if($this->buffer['control/middleware'][$name] ?? false){
				$exists=method_exists($this->buffer['control/middleware'][$name], $call[1]);
				$this->log("     {$name}->{$call[1]}()".($exists ?'' :' (✗)'));
				return ($exists) ?call_user_func_array([$this->buffer['control/middleware'][$name], $call[1]], $args) :null;
			}
			$this->log("     {$name} (✗)");
			return null;
		}
	}
}