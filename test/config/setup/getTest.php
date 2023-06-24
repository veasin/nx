<?php

use PHPUnit\Framework\TestCase;

class getTest extends TestCase{
	public function testWork1(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'a'));
		$this->assertEquals(['b'=>['c'=>1]], $result);
	}
	public function testWork2(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'a.b'));
		$this->assertEquals(['c'=>1], $result);
	}
	public function testWork3(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'a.b.c'));
		$this->assertEquals(1, $result);
	}
	public function testDefault1(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'a.b.c.d'), 2);
		$this->assertEquals(2, $result);
	}
	public function testDefault2(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'b'), 2);
		$this->assertEquals(2, $result);
	}
	public function testDefault3(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', 'a.c'), 2);
		$this->assertEquals(2, $result);
	}
	public function testDefault4(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, explode('.', ''), 2);
		$this->assertEquals(2, $result);
	}
	public function testDefault5(){
		$data =['a'=>['b'=>['c'=>1]]];
		$result =\nx\helpers\config\setup::_get($data, [], 2);
		$this->assertEquals(2, $result);
	}
}
