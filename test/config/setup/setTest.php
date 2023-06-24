<?php

use PHPUnit\Framework\TestCase;

class setTest extends TestCase{
	public function testWork1(){
		$data =[];
		$ok =\nx\helpers\config\setup::_set($data, explode('.', 'a'), 1);
		$this->assertEquals(['a'=>1], $data);
		$this->assertEquals(true, $ok);
	}
	public function testWork2(){
		$data =[];
		$ok =\nx\helpers\config\setup::_set($data, explode('.', 'a.b'), 1);
		$this->assertEquals(['a'=>['b'=>1]], $data);
		$this->assertEquals(true, $ok);
	}
	public function testWork3(){
		$data =['a'=>2];
		$ok =\nx\helpers\config\setup::_set($data, explode('.', 'a.b.c'), 1);
		$this->assertEquals(['a'=>['b'=>['c'=>1]]], $data);
		$this->assertEquals(true, $ok);
	}
	public function testNoWork(){
		$data =['a'=>2];
		$ok =\nx\helpers\config\setup::_set($data, explode('.', ''), 1);
		$this->assertEquals(['a'=>2], $data);
		$this->assertEquals(false, $ok);
	}
	public function testNoWork3(){
		$data =['a'=>2];
		$ok =\nx\helpers\config\setup::_set($data, explode('.', '.a'), 1);
		$this->assertEquals(['a'=>2], $data);
		$this->assertEquals(false, $ok);
	}
	public function testNoWork2(){
		$data =['a'=>2];
		$ok =\nx\helpers\config\setup::_set($data, [], 1);
		$this->assertEquals(['a'=>2], $data);
		$this->assertEquals(false, $ok);
	}
}
