<?php
namespace nx\parts\log;

/**
 * Class dump
 * @trait app
 * @package nx\log
 */
trait dump{
	public function log($var):void{
		var_dump($var);
	}
}