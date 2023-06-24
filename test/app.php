<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/16 016
 * Time: 17:32
 */
use PHPUnit\Framework\TestCase;

class app2 extends nx\app{
	public function getBuffer($name){
		return $this->buffer[$name];
	}
	public function setBuffer($name, $value=null):void{
		$this->buffer[$name]=$value;
	}
	public function getXXX(){
		return $this->buffer['xxx']['a']['b'];
	}
}

class app extends TestCase{
	public function testApp(){
		define('AGREE_LICENSE', true);
		$app=new \nx\app();
		$this->assertIsObject($app);
	}
	public function testBuffer(){
		define('AGREE_LICENSE', true);
		$app=new app2();
		$app->setBuffer('123', 'abc');
		$this->assertEquals('abc', $app->getBuffer('123'));

		$app->setBuffer('abc', 456);
		$this->assertEquals(456, $app->getBuffer('abc'));

		$app->setBuffer('xxx', ['a'=>['b'=>'c']]);
		$this->assertEquals('c', $app->getXXX());
	}
}