<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/14 014
 * Time: 12:20
 */

namespace nx;

class exception extends \Exception{
	public $error ='';
	public function getError(){
		return $this->error;
	}
}