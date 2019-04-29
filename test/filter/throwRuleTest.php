<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/26 026
 * Time: 09:33
 */

use PHPUnit\Framework\TestCase;

class throwRuleTest extends TestCase{
	use \nx\parts\validator\filterThrow;
	/**
	 * 规则降级(简化)
	 */
	public function testRuleAbbr(){
		//type
		$rules1 =$this->_nx_filter_rules_parse([
			'type'=>['value'=>'integer'],
		]);
		$rules2 =$this->_nx_filter_rules_parse([
			'type'=>'integer',
		]);
		$rules3 =$this->_nx_filter_rules_parse([
			'integer',
		]);
		$rules4 =$this->_nx_filter_rules_parse([
			'int'
		]);
		$this->assertEquals($rules1, $rules2);
		$this->assertEquals($rules2, $rules3);
		$this->assertEquals($rules3, $rules4);
		//from
		$rule1 =$this->_nx_filter_rules_parse([
			'from'=>'body',
		]);
		$rule2 =$this->_nx_filter_rules_parse([
			'body'
		]);
		$this->assertEquals($rule1, $rule2);
	}
	/**
	 * 使用同类型重复规则会覆盖前一个设置
	 */
	public function testRuleTypeRepeat(){
		$rule =$this->_nx_filter_rules_parse([
			'type'=>'string',
			'int','uint','str','arr','obj','json','date','hex','base64', 'integer'//type=>int, type=>unit .....
		]);
		$this->assertEquals($rule, ['type'=>['value'=>'integer']]);
	}
	/**
	 * 同上
	 */
	public function testRuleFromAbbr(){
		$rule =$this->_nx_filter_rules_parse([
			'from'=>['any'=>'string'],
			'body','query','cookie','uri','cookie'
		]);
		$this->assertEquals($rule, ['from'=>'cookie']);
	}
	/**
	 * callback简写逻辑
	 */
	public function testRuleCallback(){
		$cb =function(){};
		$rules1 =$this->_nx_filter_rules_parse([
			$cb
		]);
		$rules2 =$this->_nx_filter_rules_parse([
			'callback'=>$cb
		]);
		$rules3 =$this->_nx_filter_rules_parse([
			'callback'=>['value'=>$cb]
		]);
		$this->assertEquals($rules1, $rules2);
		$this->assertEquals($rules2, $rules3);
		$this->assertEquals($rules3, ['callback'=>['value'=>$cb]]);
	}
	public function testJson(){
		$rules1 =$this->_nx_filter_rules_parse([
			'type'=>['value'=>'json', 'type'=>'array'],
		]);
		$rules2 =$this->_nx_filter_rules_parse([
			'json'=>'array',
		]);
		$this->assertEquals($rules1, $rules2);
		$this->assertEquals($rules2, ['type'=>['value'=>'json', 'type'=>'array']]);
	}
	public function testRuleUnknow(){
		$rules =$this->_nx_filter_rules_parse([
			'123','xx','unknow','idon\'t','w'=>123,'x'=>'axxx','y'=>[123]
		]);
		$this->assertEquals($rules, [
			123=>[],
			'xx'=>[],
			'unknow'=>[],
			'idon\'t'=>[],
			'w'=>['value'=>123],
			'x'=>['value'=>'axxx'],
			'y'=>[123]
		]);
		// ???
		$this->assertEquals(['123'=>1], [123=>1]);
		$this->assertEquals(['123'=>1]=== [123=>1], true);
		$this->assertEquals(array_merge(['123'=>1], [123=>1]), [1,1]);
		$this->assertEquals([123=>1]+['123'=>1], [123=>1]);
	}
	public function testRuleNull(){
		$rules =$this->_nx_filter_rules_parse(['throw']);
		$this->assertEquals(['null'=>['value'=>'throw']], $rules);

		$rules =$this->_nx_filter_rules_parse(['default']);
		$this->assertEquals(['null'=>['value'=>'default', 'default'=>null]], $rules);

		$rules =$this->_nx_filter_rules_parse(['default'=>1]);
		$this->assertEquals(['null'=>['value'=>'default', 'default'=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['default'=>[1,2,3]]);
		$this->assertEquals(['null'=>['value'=>'default', 'default'=>[1,2,3]]], $rules);

		$rules =$this->_nx_filter_rules_parse(['remove']);
		$this->assertEquals(['null'=>['value'=>'remove']], $rules);

	}
	public function testRuleDigit(){
		$rules =$this->_nx_filter_rules_parse(['='=>1]);
		$this->assertEquals(['digit'=>['='=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['>'=>1]);
		$this->assertEquals(['digit'=>['>'=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['<'=>1]);
		$this->assertEquals(['digit'=>['<'=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['!='=>1]);
		$this->assertEquals(['digit'=>['!='=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['>='=>1]);
		$this->assertEquals(['digit'=>['>='=>1]], $rules);

		$rules =$this->_nx_filter_rules_parse(['<='=>1]);
		$this->assertEquals(['digit'=>['<='=>1]], $rules);

	}
	public function testRuleMatch(){
		$rules =$this->_nx_filter_rules_parse(['number']);
		$this->assertEquals(['match'=>['value'=>'number']], $rules);

		$rules =$this->_nx_filter_rules_parse(['email']);
		$this->assertEquals(['match'=>['value'=>'email']], $rules);

		$rules =$this->_nx_filter_rules_parse(['url']);
		$this->assertEquals(['match'=>['value'=>'url']], $rules);

		$rules =$this->_nx_filter_rules_parse(['china-mobile']);
		$this->assertEquals(['match'=>['value'=>'china-mobile']], $rules);

		$rules =$this->_nx_filter_rules_parse(['china-id']);
		$this->assertEquals(['match'=>['value'=>'china-id']], $rules);

		$rules =$this->_nx_filter_rules_parse(['ip-v4']);
		$this->assertEquals(['match'=>['value'=>'ip-v4']], $rules);

		$rules =$this->_nx_filter_rules_parse(['ip-v6']);
		$this->assertEquals(['match'=>['value'=>'ip-v6']], $rules);

	}
}