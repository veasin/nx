<?php
namespace nx\router;

trait ca{
	public function router(){
		$this->control([
			isset($_GET['c']) ?$_GET['c'] :'index',
			isset($_GET['a']) ?$_GET['a'] :'index',
		]);
	}
}