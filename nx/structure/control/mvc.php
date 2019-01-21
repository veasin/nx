<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 10:20
 */
namespace nx\structure\control;

trait mvc{
	/**
	 * @param callable|array|null $call
	 * @param mixed               ...$args
	 * @return mixed
	 * @deprecated 2019-01-21 仅仅为了兼容
	 */
	public function control($call, ...$args){
		if(is_callable($call)){//['\api\user','check'],[$this, 'main'],
			return call_user_func_array($call, $args);
		}elseif(isset($call[1]) && $call[1] instanceof \Closure){//[null, function(){}],
			return call_user_func_array($call[1]->bindTo($call[0] ?? $this), $args);
		}else{//todo call controller->action
			if(isset($call[0]) && isset($call[1]) && is_string([$call[0]]) && is_string([$call[0]])){//['user','check'],
				$class=get_class($this);
				$_controller=substr($class, 0, strrpos($class, '\\')).'\controllers\\'.$call[0];
				$exists=class_exists($_controller, true);
				if($exists){
					$controller=new $_controller($call, $this);
					return $controller->exec($call[1], true, true);
				}
			}
		}
	}
}