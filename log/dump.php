<?php
namespace nx\log;

/**
 * Class dump
 * @trait app
 * @package nx\log
 */
trait dump{
	public function log($var){
		var_dump($var);
	}
}