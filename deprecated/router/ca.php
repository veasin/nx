<?php
namespace nx\router;

/**
 * Class ca
 * @trait app
 * @package nx\router
 * @deprecated 2019-04-17
 */
trait ca{
	public function router(){
		$this->control([
			isset($_GET['c']) ?$_GET['c'] :'index',
			isset($_GET['a']) ?$_GET['a'] :'index',
			[],
		]);
	}
}