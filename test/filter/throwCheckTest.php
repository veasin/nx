<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class throwCheckTest extends TestCase{
	use \nx\validator\filterThrow;
	/**
	 * 空值(null)检测
	 */
	public function testNullDefault(){
		$value =$this->_nx_filter_key_check(null, $this->_nx_filter_rules_parse(['null'=>1]));
		$this->assertEquals(1, $value[1]);
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
}