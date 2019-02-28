<?php

namespace nx;

use nx\base\o2;

class output implements \ArrayAccess, \Countable, \IteratorAggregate{
	use o2;
	public $buffer;
	private $callback =null;
	private $hasRender =false;
	public function put($key, $value){
		$this->data[$key] =$value;
	}
	public function get($key =null){
		if(null ===$key) return $this->data;
		return $this->data[$key] ?? null;
	}
	public function setRender(callable $callback){
		$this->callback =is_callable($callback) ?$callback :null;
	}
	public function render(){
		$this->hasRender =true;
		if($this->callback){
			ob_start();
			call_user_func($this->callback, $this);
			$r=ob_get_clean();
		} else $r='';
		return $r;
	}
	public function __toString(){
		return $this->render();
	} //echo $this
	public function __destruct(){
		if(!$this->hasRender && $this->callback) call_user_func($this->callback, $this);
	}
}