<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class throwTest extends TestCase{
	use \nx\validator\filterThrow;
	protected $in;
	protected $source=[
		'string'=>"123456789",
		'string2'=>"12345678x",
		'email'=>'vea.urn2@gmail.com',
		'mobile'=>17090084418,
		'id'=>'000000198103230000',
		'hex'=>'0xFFFF',
		'int1'=>1,
		'int2'=>10,
		'cid'=>11,
		'did'=>12,
	];
	protected function setUp(){
		parent::setUp(); // TODO: Change the autogenerated stub
		$this->in =new \nx\input();
		$this->in['query']=[
			'cid'=>1,
			'did'=>2,
		];
		$this->in['body']=[
			'string'=>"123456789",
			'string2'=>"12345678x",
			'email'=>'vea.urn2@gmail.com',
			'mobile'=>17090084418,
			'id'=>'000000198103230000',
			'date1'=>'2019-03-07 15:17',
			'date2'=>'2019-03-07 15:17:05',
			'date3'=>'2019/03/07',
			'date4'=>'- xx d 2019x/03/07',
			'hex'=>'0xFFFF',
			'int1'=>1,
			'int2'=>10,
			'cid'=>3,
			'did'=>4,
			'json1'=>json_encode('12345', JSON_UNESCAPED_UNICODE),
			'json2'=>json_encode(true, JSON_UNESCAPED_UNICODE),
			'json3'=>json_encode(null, JSON_UNESCAPED_UNICODE),
			'json4'=>json_encode([1,2,3], JSON_UNESCAPED_UNICODE),
			'json5'=>json_encode(['id'=>1, 'xx'=>2], JSON_UNESCAPED_UNICODE),
			'json6'=>json_encode(['id'=>1, 'xx'=>'x'], JSON_UNESCAPED_UNICODE),
			'array1'=>[1,2,3],
			'array2'=>['id'=>1, 'xx'=>2],
			'array3'=>['id'=>1, 'xx'=>'x'],
			'array4'=>'1,2,3,4,5',
			'array5'=>'1,2,3,4,5,xx',
			'base64'=>base64_encode('12345'),
		];
		$this->in['params']=[
			'cid'=>5,
			'did'=>6,
		];
		$this->in['header']=[
			'cid'=>7,
			'did'=>8,
		];
		$this->in['cookie']=[
			'cid'=>9,
			'did'=>10,
		];
	}
	/**
	 * 规则降级(简化)
	 */
	public function testRules(){
		$data =$this->filter([
			'cxx-id'=>[
				['rule'=>'type', 'value'=>'integer'],
				['rule'=>'from', 'value'=>'query'],
				['rule'=>'name', 'value'=>'cid'],
			],
			'dxx-id'=>[
				'type'=>['value'=>'integer'],
				'from'=>['value'=>'query'],
				'name'=>['value'=>'did'],
			],
			'cx-id'=>[
				'type'=>'integer',
				'from'=>'body',
				'name'=>'cid',
			],
			'dx-id'=>['integer','body','name'=>'did',],
			'cid'=>['int','uri'],
			'did'=>['int', 'uri'],
			'int2'=>'int',
		]);
		$this->assertEquals(1, $data['cxx-id']);
		$this->assertEquals(2, $data['dxx-id']);
		$this->assertEquals(3, $data['cx-id']);
		$this->assertEquals(4, $data['dx-id']);
		$this->assertEquals(5, $data['cid']);
		$this->assertEquals(6, $data['did']);
		$this->assertEquals(10, $data['int2']);
	}
	/**
	 * 不同来源
	 */
	public function testFrom(){
		//todo header & cookie
		$data =$this->filter([
			'cid1'=>['int', 'query', 'name'=>'cid'],
			'cid2'=>['int', 'body', 'name'=>'cid'],
			'cid3'=>['int', 'uri', 'name'=>'cid'],
			'cid4'=>['int', 'header', 'name'=>'cid'],
			'cid5'=>['int', 'cookie', 'name'=>'cid'],
			'cid6'=>['int', 'source'=>$this->source, 'name'=>'cid'],
			'cid7'=>['int', 'name'=>'cid'],//<----------default 'body'
		]);
		$this->assertEquals(1, $data['cid1']);
		$this->assertEquals(3, $data['cid2']);
		$this->assertEquals(5, $data['cid3']);
		$this->assertEquals(7, $data['cid4']);
		$this->assertEquals(9, $data['cid5']);
		$this->assertEquals(11, $data['cid6']);
		$this->assertEquals(3, $data['cid7']);
	}
	/**
	 * 调整默认来源
	 */
	public function testFromDefault(){
		//todo header & cookie
		$data =$this->filter([
			'cid1'=>['int', 'query', 'name'=>'cid'],
			'cid2'=>['int', 'name'=>'cid'],//<----------default 'query'
			'cid3'=>['int', 'uri', 'name'=>'cid'],
			'cid4'=>['int', 'header', 'name'=>'cid'],
			'cid5'=>['int', 'cookie', 'name'=>'cid'],
			'cid6'=>['int', 'source'=>$this->source, 'name'=>'cid'],
			'cid7'=>['int', 'name'=>'cidx'],//<----------no exist
			'cid8'=>['int', 'name'=>'cidx', 'default'=>1],//<----------no exist set default
		], ['query', 'throw'=>404]);
		$this->assertEquals(1, $data['cid1']);
		$this->assertEquals(1, $data['cid2']);
		$this->assertEquals(5, $data['cid3']);
		$this->assertEquals(7, $data['cid4']);
		$this->assertEquals(9, $data['cid5']);
		$this->assertEquals(11, $data['cid6']);
		$this->assertEquals(null, $data['cid7']);
		$this->assertEquals(1, $data['cid8']);//上层throw不会影响到当前默认值设置
	}
	/**
	 * 默认值设置
	 */
	public function testDefault(){
		//数据返回 全局数据来源配置
		$data =$this->filter('xid', ['default'=>2, 'query']);
		$this->assertEquals(2, $data);
	}
	/**
	 * 必填
	 * @expectedException \Exception
	 * @expectedExceptionCode 400
	 */
	public function testDefaultError(){
		//数据返回 全局数据来源配置
		$this->filter('xid', ['default', 'query']);
	}
	/**
	 * 必填
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testDefaultErrorAndCustomError(){
		//数据返回 全局数据来源配置
		$this->filter('xid', ['default', 'query', 'throw'=>401]);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testIntGT10Error(){
		//数据返回 全局数据来源配置
		$this->filter('cid', ['int', '>'=>10, 'query', 'throw'=>401]);
	}
	public function testINT1(){
		//标准数组返回
		$data=$this->filter(['int1'=>['int']]);
		$this->assertEquals(1, $data['int1']);
		$this->assertInternalType('integer', $data['int1']);
	}
	public function testINT1Alias(){
		//标准数组返回
		$data=$this->filter(['int1'=>['integer']]);
		$this->assertEquals(1, $data['int1']);
		$this->assertInternalType('integer', $data['int1']);
	}
	public function testINT1NameAs(){
		//数组返回 别名
		$data=$this->filter(['int'=>['int', 'name'=>'int1', 'source'=>$this->source]]);
		$this->assertEquals(1, $data['int']);
	}
	public function testINT1Data(){
		//数据返回
		$data=$this->filter('int1', ['int', 'source'=>$this->source]);
		$this->assertEquals(1, $data);
	}
	public function testINT1DataOptions(){
		//数据返回 全局数据来源配置
		$data =$this->filter('int1', ['int'], ['source'=>$this->source]);
		$this->assertEquals(1, $data);
	}
	public function testString(){
		//标准数组返回
		$data=$this->filter(['string'=>['str', 'body']]);
		$this->assertInternalType('string', $data['string']);
	}
	public function testStringAlias(){
		//标准数组返回
		$data=$this->filter(['string'=>['string', 'body']]);
		$this->assertInternalType('string', $data['string']);
	}
	public function testJson(){
		//标准数组返回
		$data=$this->filter([
			'json1'=>['json'=>'int'],
			'json2'=>'json',
			'json3'=>'json',
			'json4'=>['json'=>['arr'=>'int']],
			'json5'=>'json',//todo [key=>value] ?
			//'json6'=>['json'=>['arr'=>['int', 'throw'=>401]]],
		], ['body']);
		//$this->assertInternalType('string', $data['json1']);
		$this->assertInternalType('integer', $data['json1']);
		$this->assertEquals('12345', $data['json1']);
		$this->assertInternalType('boolean', $data['json2']);
		$this->assertEquals(true, $data['json2']);
		$this->assertEquals(null, $data['json3']);
		$this->assertInternalType('array', $data['json4']);
		$this->assertEquals([1,2,3], $data['json4']);
		$this->assertInternalType('array', $data['json5']);
		$this->assertEquals(['id'=>1, 'xx'=>2], $data['json5']);
		//$this->assertInternalType('array', $data['json6']);
		//$this->assertEquals(['id'=>1, 'xx'=>'x'], $data['json6']);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 401
	 */
	public function testDate(){
		$data=$this->filter([
			'date1'=>'date',
			'date2'=>'date',
			'date3'=>'date',
			'date4'=>['date', 'throw'=>401],
		], ['body']);
		$this->assertInternalType('integer', $data['date1']);
		$this->assertEquals(strtotime('2019-03-07 15:17'), $data['date1']);
		$this->assertInternalType('integer', $data['date2']);
		$this->assertEquals(strtotime('2019-03-07 15:17:05'), $data['date2']);
		$this->assertInternalType('integer', $data['date3']);
		$this->assertEquals(strtotime('2019-03-07 00:00:00'), $data['date3']);
		$this->assertInternalType('integer', $data['date4']);
		$this->assertEquals(0, $data['date4']);
	}
	public function testArray(){
		$data=$this->filter([
			//'array1'=>'arr',
			//'array2'=>['array'=>['key-exists'=>'id']],
			//'array3'=>['type'=>['value'=>'array', 'throw'=>403], 'throw'=>404],//no exists idx
			'array4'=>['array'=>['int', 'throw'=>402], 'throw'=>401],
		], ['body']);
		//$this->assertInternalType('array', $data['array1']);
		//$this->assertEquals([1,2,3], $data['array1']);
		//$this->assertInternalType('array', $data['array2']);
		//$this->assertEquals(['id'=>1, 'xx'=>2], $data['array2']);
		//$this->assertInternalType('array', $data['array3']);
		//$this->assertEquals(['id'=>1, 'xx'=>'x'], $data['array3']);
		$this->assertInternalType('array', $data['array4']);
		$this->assertEquals([1,2,3,4,5], $data['array4']);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 402
	 */
	public function testArrayInt(){
		$data=$this->filter([
			'array5'=>['array'=>['int', 'throw'=>402], 'throw'=>401],
		], ['body']);
	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 402
	 */
	public function testArrayKeyNoExists(){
		$data=$this->filter([
			'array4'=>['array'=>['key-exists'=>'idx', 'throw'=>402], 'throw'=>401],
		], ['body']);
		$this->assertInternalType('array', $data['array4']);
		$this->assertEquals([1,2,3,4,5], $data['array4']);

	}
	/**
	 * @expectedException \Exception
	 * @expectedExceptionCode 402
	 */
	public function testArrayValueNoExists(){
		$data=$this->filter([
			'array4'=>['array'=>['value-exists'=>6, 'throw'=>402], 'throw'=>401],
		], ['body']);
		$this->assertInternalType('array', $data['array4']);
		$this->assertEquals([1,2,3,4,5], $data['array4']);

	}
	public function testHex(){
		//标准数组返回
		$data=$this->filter([
			'hex'=>'hex',
		], ['body']);
		//$this->assertInternalType('string', $data['json1']);
		$this->assertInternalType('integer', $data['hex']);
		$this->assertEquals(65535, $data['hex']);
	}
	public function testBase64(){
		//标准数组返回
		$data=$this->filter([
			'base64'=>'base64',
		], ['body']);
		$this->assertInternalType('string', $data['base64']);
		$this->assertEquals('12345', $data['base64']);
	}
	public function testRemove(){
		//标准数组返回
		$data=$this->filter([
			'cid'=>['int'],
			'did'=>['int'],
			'xid'=>['int', 'remove'],
		], ['body']);
		$this->assertInternalType('array', $data);
		$this->assertEquals(['cid'=>3, 'did'=>4], $data);
	}
	public function testRemoveOne(){
		//标准数组返回
		$data=$this->filter('xid', ['int', 'remove']);
		$this->assertInternalType('null', $data);
		$this->assertEquals(null, $data);
	}
	public function testRemoveAndEmpty(){
		//标准数组返回
		$data=$this->filter([
			'xid'=>['int', 'remove'],
		], ['body']);
		$this->assertInternalType('array', $data);
		$this->assertEquals([], $data);
	}
}