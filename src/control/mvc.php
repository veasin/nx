<?php
namespace nx\control;

/**
 * Class mvc
 * @trait controller
 * @package nx\control
 */
trait mvc{
	public function control($route){
		$this->log('control(mvc):'.json_encode($route));
		if(isset($route[0]) && is_string($route[0])){
			list($namespace,) =explode('\\', get_class($this), 2);
			$_controller =$namespace.'\controllers\\'.$route[0];
			$this->log(' - '.$_controller);
			return class_exists($_controller, true)? new $_controller($route, $this):new \nx\mvc\controller($route, $this);
		} elseif(is_callable($route)) return call_user_func($route, $this->resquest, $this->response);
	}
}