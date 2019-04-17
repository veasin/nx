<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/16 016
 * Time: 17:32
 */
use PHPUnit\Framework\TestCase;

class app extends TestCase{
	public function testApp(){
		define('AGREE_LICENSE', true);

		$app =new \nx\app();
		$this->assertInternalType('object',$app);
	}
}