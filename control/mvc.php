<?php
namespace nx\control;

trait mvc{
	public function control($route){
		if(isset($route[0]) && is_string($route[0])){
			$namespace =dirname(get_class($this));
			$_controller =$namespace.'\controllers\\'.$route[0];
			return class_exists($_controller, true)? new $_controller($route, $this):new \nx\mvc\controller($route, $this);
		} elseif(is_callable($route)) return call_user_func($route, $this->resquest, $this->response);
	}
}