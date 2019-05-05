<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class throwCheckTest extends TestCase{
	use \nx\parts\validator\filterThrow;
	/**
	 * 空值(null)检测
	 */
	public function testNullDefault(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>1]));
		$this->assertEquals(1, $value[1]);
		//触发松散比较 fix
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>0]));
		$this->assertEquals(0, $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>true]));
		$this->assertEquals(true, $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>false]));
		$this->assertEquals(false, $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>'123']));
		$this->assertEquals('123', $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>['value'=>'default', 'default'=>[]]]));
		$this->assertEquals([], $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['default'=>[]]));
		$this->assertEquals([], $value[1]);

		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>""]));
		$this->assertEquals("", $value[1]);
	}
	/**
	 * 空值(null)移除
	 */
	public function testNullRemove(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>'remove']));
		$this->assertEquals(true, $value[0]);
	}
	/**
	 * 空值(null)报错 并定义错误code
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testNullThrow(){
		$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>'throw', 'error'=>401]));
	}
	/**
	 * 空值(null)报错 并定义错误code
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testNullThrowNull(){
		$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>null, 'error'=>401]));
	}
	/**
	 * 空值(empty)检测
	 */
	public function testEmptyDefault(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['empty'=>1]));
		$this->assertEquals(1, $value[1]);
	}
	/**
	 * 空值(empty)移除
	 */
	public function testEmptyRemove(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['empty'=>'remove']));
		$this->assertEquals(true, $value[0]);
	}
	/**
	 * 空值(empty)报错 并定义错误code
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testEmptyThrow(){
		$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['empty'=>'throw', 'error'=>401]));
	}
	/**
	 * 回调修改内容
	 */
	public function testCallback(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse([
			function(){
				return 1;
			}
		]));
		$this->assertEquals(1, $value[1]);
	}
	/**
	 * 回调修改内容
	 */
	public function testCallbackKey(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse([
			function($value, $key, $throw){
				return $key;
			}
		]), 'key');
		$this->assertEquals('key', $value[1]);
	}
	/**
	 * 回调修改内容
	 * @expectedException \Exception
	 * @expectedExceptionCode 402
	 */
	public function testCallbackThrow(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse([
			function($value, string $key, callable $throw, callable $getValue){
				$throw('自定义错误', 402);
			}
		]), 'key');
		$this->assertEquals('key', $value[1]);
	}
	/**
	 * 回调修改内容
	 */
	public function testCallbackGetValue(){
		$value =$this->filter([
			'password'=>function($value, string $key, callable $throw, callable $getValue){
				return $value ===$getValue('re-password');
			}
		], ['from'=>[
			'password'=>'123456',
			're-password'=>'123456',
		]]);
		$this->assertEquals(true, $value['password']);
	}
	public function testLengthEqual(){
		$value =$this->_nx_filter_key_check('12345', $this->_nx_filter_rules_parse(['length'=>5]));
		$this->assertEquals('12345', $value[1]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 403
	 */
	public function testLengthEqualThrow(){
		$value =$this->_nx_filter_key_check('1234', $this->_nx_filter_rules_parse(['length'=>5, 'error'=>403]));
		$this->assertEquals('1234', $value[1]);
	}
	public function testLengthGt(){
		$value =$this->_nx_filter_key_check('12345', $this->_nx_filter_rules_parse(['length'=>['>'=>4]]));
		$this->assertEquals('12345', $value[1]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 403
	 */
	public function testLengthGtThrow(){
		$value =$this->_nx_filter_key_check('1234', $this->_nx_filter_rules_parse(['length'=>['>'=>4], 'error'=>403]));
		$this->assertEquals('1234', $value[1]);
	}
	public function testLengthLt(){
		$value =$this->_nx_filter_key_check('12345', $this->_nx_filter_rules_parse(['length'=>['<'=>6]]));
		$this->assertEquals('12345', $value[1]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 403
	 */
	public function testLengthLtThrow(){
		$value =$this->_nx_filter_key_check('1234', $this->_nx_filter_rules_parse(['length'=>['<'=>4], 'error'=>403]));
		$this->assertEquals('1234', $value[1]);
	}
	public function testDigitGt(){
		$value =$this->_nx_filter_key_check(5, $this->_nx_filter_rules_parse(['digit'=>['>'=>4]]));
		$this->assertEquals(5, $value[1]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 400
	 */
	public function testDigitGtZero(){
		$value =$this->_nx_filter_key_check(0, $this->_nx_filter_rules_parse(['digit'=>['>'=>0]]));
		//$this->assertEquals(0, $value[1]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 404
	 */
	public function testDigitGtThrow(){
		$value =$this->_nx_filter_key_check(3, $this->_nx_filter_rules_parse(['digit'=>['>'=>4], 'error'=>404]));
		//$this->assertEquals(3, $value[1]);
	}
}