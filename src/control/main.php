<?php
namespace nx\control;

/**
 * Class mvc
 * @trait controller
 * @package nx\control
 */
trait main{
	/**
	 * @param array $route
	 *
	 * @return mixed
	 */
	public function control($route=[]){
		$this->log('control(main):'.json_encode($route));
		return call_user_func_array([$this, 'main'], [$route]);
	}
}