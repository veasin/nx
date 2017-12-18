<?php
namespace nx\control;

/**
 * Class mvc
 * @trait controller
 * @package nx\control
 */
trait mvc{
	public function control($route, $path='controllers\\'){
		$this->log('    - control(mvc):'.json_encode($route));
		if(isset($route[0]) && is_string($route[0])){
			$class =get_class($this);
			$_controller =substr($class, 0, strrpos($class, '\\')).'\\'.$path.$route[0];
			$exists =class_exists($_controller, true);
			$this->log($exists ? '      found: '.$_controller: '      no: '.$_controller);
			$controller =$exists? new $_controller($route, $this):new \nx\mvc\controller($route, $this);
			return $controller->exec($route[1], true, true);
		} elseif(is_callable($route)) return call_user_func($route, $this->resquest, $this->response);
	}
}