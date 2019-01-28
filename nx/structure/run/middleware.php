<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/2 002
 * Time: 09:18
 */
namespace nx\structure\run;

/**
 * Trait next
 * @package nx\control
 */
trait middleware{
	public function run($router=null, ...$args){
		$router=$router ?? $this->router;
		$g=$router->next();
		$next=function(...$_args) use (&$next, $g, $args){
			if($g->valid()){
				$call=$g->current();
				$g->next();
				$result=$this->control($call, $next, ...$_args, ...$args);
			}else $result=$args[0] ?? null;
			return $result;
		};
		return $next();
	}
}