<?php

namespace nx\parts\control;
/**
 * @method mixed main(...$args)
 * @method void runtime(string $info, string $from)
 */
trait main{
	public function control(mixed $call = null, ...$args): mixed{
		if(is_callable($call)){
			//$this->runtime("control: $call()");
			return call_user_func_array($call, $args);
		} else {
			$this->runtime("control: main()", 'main');
			return $this->main($call, $args);
		}
	}
}