<?php
namespace nx\parts\model;

trait cache{
	protected $cacheModel=[];
	protected function cacheModel(string $ModelNameSpace, ...$args){
		if(!array_key_exists($ModelNameSpace, $this->cacheModel)){
			$this->cacheModel[$ModelNameSpace]=new $ModelNameSpace(...$args);
		}
		return $this->cacheModel[$ModelNameSpace];
	}
}
