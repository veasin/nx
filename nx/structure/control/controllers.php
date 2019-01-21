<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 10:20
 */
namespace nx\structure\control;

trait controllers{
	protected $trait_controllers=[];
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
				$class=get_class($this);
				$r =strrpos($class, '\\');
				$_controller=substr($class, 0, $r?$r:strlen($class)).'\controllers\\'.$call[0];
				if(!array_key_exists($_controller, $this->trait_controllers)){
					$exists=class_exists($_controller, true);
					$this->trait_controllers[$_controller] =$exists ?new $_controller($this) :null;
					$this->logger->debug('control: {controller} {exist}', ['controller'=>$_controller, 'exist'=>$exists ?'true' :'false']);
				}
				$this->logger->debug('control call: ->{action}()', ['action'=>$call[1]]);
				return isset($this->trait_controllers[$_controller]) ?call_user_func_array([$this->trait_controllers[$_controller], $call[1]], $args) :null;
			}
		}
	}
}

/*$result =$controller::factiory($this, $call, $args);
class xxx{
	public function factory($app, $call, $args){
		$controller =new static($app, $call);

		//on,get,before

		return call_user_func_array([$controller, $call[1]], $args);
	}
}*/
