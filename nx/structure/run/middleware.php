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
		$stack=[];
		$result=null;
		$router=$router ?? $this->router;
		$g=$router->next();
		$next=function(...$_args) use ($stack, &$next, $g, $args){
			if($g->valid()){
				$call=$g->key();
				$call_args=$g->current();
				$as=array_merge([$next], $_args, $call_args, $args);
				$g->next();
				$result=$this->control($call, ...$as);
			}else $result=$args[0] ?? null;
			return $result;
		};
		return $next();
	}
}