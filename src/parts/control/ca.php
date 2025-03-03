<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 10:20
 */
namespace nx\parts\control;

/**
 * @method void runtime(string $info, string $from)
 */
trait ca{
	/**
	 * @param callable|array|null $call
	 * @param mixed               ...$args
	 * @return mixed
	 */
	public function control(mixed $call=null, ...$args): mixed{
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
			if(!isset($this['control:ca'])) $this['control:ca']=[];
			$ca =&$this['control:ca'];
			if(!array_key_exists($name, $ca)){
				$ca[$name]=class_exists($name, true) ?new $name($this) :null;
			}
			if($ca[$name] ?? false){
				$exists=method_exists($ca[$name], $call[1]);
				$this->runtime("  $name->$call[1]()".($exists ?'' :' (✗)'), 'ca');
				return ($exists) ?$ca[$name]->{$call[1]}(...$args) :null;
			}
			$this->runtime("  $name (✗)", 'ca');
			return null;
		}
		return null;
	}
}