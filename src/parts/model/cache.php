<?php
namespace nx\parts\model;

trait cache{
	protected array $_cache_model=[];
	protected function cacheModel(string $ModelNameSpace, ...$args){
		if(!array_key_exists($ModelNameSpace, $this->_cache_model)){
			$this->_cache_model[$ModelNameSpace]=new $ModelNameSpace(...$args);
		}
		return $this->_cache_model[$ModelNameSpace];
	}
}
