<?php
namespace nx\control;

/**
 * Class mvc
 * @trait controller
 * @package nx\control
 * @deprecated 2019-02-28
 */
trait mvc{
	public function control($call=null, ...$args){
		$this->log('    - control(mvc):'.json_encode($call));
		if(isset($call[0]) && is_string($call[0])){
			$class =get_class($this);
			$_controller =substr($class, 0, strrpos($class, '\\')).'\\controllers\\'.$call[0];
			$exists =class_exists($_controller, true);
			$this->log($exists ? '      found: '.$_controller: '      no: '.$_controller);
			$controller =$exists? new $_controller($call, $this):new \nx\mvc\controller($call, $this);
			return $controller->exec($call[1], true, true);
		} elseif(is_callable($call)) return call_user_func($call);
	}
}