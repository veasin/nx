<?php
namespace nx\log;

/**
 * Class dump
 * @trait app
 * @package nx\log
 * @deprecated 2019-04-17
 */
trait dump{
	public function log($var){
		var_dump($var);
	}
}