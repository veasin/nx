<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class throwGetValueTest extends TestCase{
	use \nx\parts\validator\filterThrow;
	protected $in;
	protected $source=[
		'cid'=>11,
		'did'=>12,
		'array1'=>[1,2,3],
		'array2'=>['id'=>1, 'xx'=>2],
		'array3'=>['id'=>1, 'xx'=>'x'],
		'array4'=>'1,2,3,4,5',
		'array5'=>'1,2,3,4,5,xx',
		'array6'=>[
			'list'=>[
				['id'=>1],
				['id'=>2],
				['id'=>3],
			]
		],
		'array7'=>[
			'id'=>1,
			'xx'=>[
				'a'=>[
					['n'=>'1'],
					['n'=>'2'],
					['n'=>'3'],
				],
				'b'=>3,
			]
		],
	];
	/**
	 * 整形测试
	 */
	public function testTypeInt(){
		$rules =['type'=>['value'=>'integer']];

		$value =$this->_nx_filter_change_type(1, $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(1, $value);

		$value =$this->_nx_filter_change_type(-1, $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(-1, $value);

		$value =$this->_nx_filter_change_type('-1', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(-1, $value);

		$value =$this->_nx_filter_change_type('+1', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(1, $value);

		$value =$this->_nx_filter_change_type('0', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(0, $value);

		$value =$this->_nx_filter_change_type('0123', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(123, $value);
		//char A a string
		$value =$this->_nx_filter_change_type('A', $rules);
		//$this->assertInternalType('integer', $value);
		$this->assertEquals(null, $value);
	}
	public function testUnsigned(){
		$rules =['type'=>['value'=>'unsigned']];

		$value =$this->_nx_filter_change_type(1, $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(1, $value);

		$value =$this->_nx_filter_change_type(-1, $rules);
		$this->assertEquals(null, $value);

		$value =$this->_nx_filter_change_type('-1', $rules);
		$this->assertEquals(null, $value);

		$value =$this->_nx_filter_change_type('+1', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(1, $value);

		$value =$this->_nx_filter_change_type('0', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(0, $value);

		$value =$this->_nx_filter_change_type('0123', $rules);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(123, $value);
	}
	public function testJsonInt(){
		//json => integer
		$value =$this->_nx_filter_change_type(1, ['type'=>['value'=>'json', 'type'=>'int']]);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(1, $value);
		//json => integer
		$value =$this->_nx_filter_change_type("123", ['type'=>['value'=>'json', 'type'=>'int']]);
		$this->assertInternalType('integer', $value);
		$this->assertEquals(123, $value);
		//json => integer
		$value =$this->_nx_filter_change_type("false", ['type'=>['value'=>'json', 'type'=>'int']]);
		//$this->assertInternalType('boolean', $value);
		$this->assertEquals(null, $value);
		//json => false
		$value =$this->_nx_filter_change_type("false", ['type'=>['value'=>'json']]);
		$this->assertInternalType('boolean', $value);
		$this->assertEquals(false, $value);
	}
	public function testHex(){
		//ff => 255
		$value =$this->_nx_filter_change_type('ff', ['type'=>['value'=>'hex']]);
		$this->assertEquals(255, $value);
		//0xff => 255
		$value =$this->_nx_filter_change_type('0xff', ['type'=>['value'=>'hex']]);
		$this->assertEquals(255, $value);
		//12 => 255
		$value =$this->_nx_filter_change_type('12', ['type'=>['value'=>'hex']]);
		$this->assertEquals(18, $value);
		//0xZZZ => 0
		$value =$this->_nx_filter_change_type('0xZZZ', ['type'=>['value'=>'hex']]);
		$this->assertEquals(0, $value);//no error => 0
		//0xZZZ => 0
		$value =$this->_nx_filter_change_type('ZZZ', ['type'=>['value'=>'hex']]);
		$this->assertEquals(0, $value);//no error => 0
	}
	public function testBase64(){
		$value =$this->_nx_filter_change_type(base64_encode('12345'), ['type'=>['value'=>'base64']]);
		$this->assertEquals('12345', $value);
		$value =$this->_nx_filter_change_type(base64_encode('中文'), ['type'=>['value'=>'base64']]);
		$this->assertEquals('中文', $value);
		$value =$this->_nx_filter_change_type('中文', ['type'=>['value'=>'base64']]);
		$this->assertEquals(null, $value);
	}
	public function testDate(){
		$rules =['type'=>['value'=>'date']];

		$value =$this->_nx_filter_change_type('2019-03-07 15:17', $rules);
		$this->assertEquals(strtotime('2019-03-07 15:17'), $value);

		$value =$this->_nx_filter_change_type('2019-03-07 15:17:05', $rules);
		$this->assertEquals(strtotime('2019-03-07 15:17:05'), $value);

		$value =$this->_nx_filter_change_type('2019/03/07', $rules);
		$this->assertEquals(strtotime('2019/03/07'), $value);

		$value =$this->_nx_filter_change_type('+1 day', $rules);
		$this->assertEquals(strtotime('+1 day'), $value);

		$value =$this->_nx_filter_change_type('- xx d 2019x/03/07', $rules);
		$this->assertEquals(null, $value);
	}
	public function testArray(){
		//default array, filter children int
		$value =$this->_nx_filter_change_type($this->source['array1'], $this->_nx_filter_rules_parse(['values'=>'int']));
		$this->assertEquals([1,2,3], $value);
		//value array, filter children int
		$value =$this->_nx_filter_change_type($this->source['array2'], $this->_nx_filter_rules_parse(['values'=>'int']));
		$this->assertEquals(['id'=>1, 'xx'=>2], $value);
		//value array, filter children int, 'x' =>null
		$value =$this->_nx_filter_change_type($this->source['array3'], $this->_nx_filter_rules_parse(['values'=>'int']));
		$this->assertEquals(['id'=>1, 'xx'=>null], $value);
		//string =>array, filter children int
		$value =$this->_nx_filter_change_type($this->source['array4'], $this->_nx_filter_rules_parse(['values'=>'int']));
		$this->assertEquals([1,2,3,4,5], $value);
		//string =>array, filter children int, 'x'=>null
		$value =$this->_nx_filter_change_type($this->source['array5'], $this->_nx_filter_rules_parse(['values'=>'int']));
		$this->assertEquals([1,2,3,4,5,null], $value);
		//key-value array, id => int, xx no filter
		$value =$this->_nx_filter_change_type($this->source['array3'], $this->_nx_filter_rules_parse(['keys'=>[
			'id'=>'int',
			'xx'=>[],
			'xxx'=>[],
		]]));
		$this->assertEquals(['id'=>1, 'xx'=>'x', 'xxx' => null], $value);
		//key-value array, only return id
		$value =$this->_nx_filter_change_type($this->source['array3'], $this->_nx_filter_rules_parse(['keys'=>[
			'id'=>'int',
		]]));
		$this->assertEquals(['id'=>1], $value);
	}
	public function testArrayMult(){
		//key-value array, only return id
		$value =$this->_nx_filter_change_type($this->source['array6'], $this->_nx_filter_rules_parse([
			'array'=>['children'=>[
				'list'=>['array'=>['children'=>[
					['n'=>'int'],
				]]]
			]]
		]));
		$this->assertEquals([
			'list'=>[
				['id'=>1],
				['id'=>2],
				['id'=>3],
			]
		], $value);
		$value =$this->_nx_filter_change_type($this->source['array7'],  $this->_nx_filter_rules_parse(['object'=>[
			'id'=>'int',
			'xx'=>['object'=>[
				'a'=>[
					'array'=>['object'=>[
						'n'=>['int']
					]]
				],
				'b'=>'int',
			]]
		]]));
		$this->assertEquals([
			'id'=>1,
			'xx'=>[
				'a'=>[
					['n'=>1],
					['n'=>2],
					['n'=>3],
				],
				'b'=>3,
			]
		], $value);

		//$value =$this->_nx_filter_change_type($this->source['array7'], ['keys'=>[
		//	'id'=>'int',
		//	'xx'=>['{}'=>[
		//		'a'=>[
		//			'[]'=>['{}'=>[
		//				'n'=>'int',
		//			]]
		//		],
		//		'b'=>'int',
		//	]]
		//]]);

		//$values =$this->filter([
		//	'id'=>['int'],
		//	'xx'=>[[// -keys
		//		'a'=>[
		//			'values'=>[[// -keys
		//				'n'=>'int'
		//			]]
		//		],
		//		'b'=>['int']
		//	]],
		//], ['from'=>$this->source['array7']]);


		//$this->assertEquals([
		//	'list'=>[
		//		['id'=>1],
		//		['id'=>2],
		//		['id'=>3],
		//	]
		//], $value);

		//key-value array, only return id
		//$value =$this->_nx_filter_change_type($this->source['array6'], [
		//	'type'=>['value'=>'array', 'children'=>[
		//		'id'=>'int',
		//		'xx'=>['type'=>'array', 'children'=>[
		//			'a'=>['type'=>'array', 'children'=>[
		//				['type'=>'array', 'children'=>[
		//					'n'=>'int'
		//				]]
		//			]],
		//			'b'=>'int'
		//		]]
		//	]]
		//]);
		//$this->assertEquals([
		//	'id'=>1,
		//	'xx'=>[
		//		'a'=>[
		//			['n'=>1],
		//			['n'=>2],
		//			['n'=>3],
		//		],
		//		'b'=>3,
		//	]
		//], $value);

	}
}