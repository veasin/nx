<?php
namespace nx\control;

/**
 * Class mvc
 * @trait controller
 * @package nx\control
 * @deprecated 2019-02-28
 */
trait main{
	/**
	 * @param null  $call
	 * @param mixed ...$args
	 * @return mixed
	 */
	protected function control($call=null, ...$args){
		return call_user_func_array($call ?? [$this, 'main'], $args);
	}
}