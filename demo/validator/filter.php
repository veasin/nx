<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/03/20 020
 * Time: 11:04
 */

define('AGREE_LICENSE', true);//框架常量
error_reporting(E_ALL);//错误报告
date_default_timezone_set('Asia/Shanghai');//设定默认时区

require '../../src/autoload.php';//框架自动加载路径，可使用composer替换
nx\autoload::register([]);//自动加载注册，可在其中指定命名空间第一段指向目录

class app extends \nx\app{//框架的根基是trait 需要先use，如果没有use任何的，默认为404直接报错
	var $ver =1.6;
	use \nx\validator\filterThrow;

	public function main(){
		echo '<pre>';
		try{

			//$v1=$this->filter([
			//	'category_id'=>[
			//		['rule'=>'from', 'value'=>'query', 'name'=>'cid', 'throw'=>401],
			//		['rule'=>'type', 'value'=>'integer'],
			//		['rule'=>'default', 'value'=>null, 'throw'=>402],
			//		['rule'=>'>', 'value'=>0],
			//		'throw'=>403,
			//	],
			//], ['throw'=>404]);
			//$v2=$this->filter([
			//	'category_id'=>[
			//		'from'=>['value'=>'query', 'name'=>'cid'],
			//		'type'=>['value'=>'integer'],
			//		'default'=>['value'=>null],
			//		'>'=>['value'=>0],
			//	]
			//]);
			//$v3=$this->filter([
			//	'category_id'=>[
			//		'from'=>['value'=>'query'],
			//		'name'=>['value'=>'cid'],
			//		'type'=>['value'=>'integer'],
			//		'default'=>['value'=>null],
			//		'>'=>['value'=>0],
			//	]
			//]);
			//$v4=$this->filter([
			//	'category_id'=>[
			//		'from'=>'query',
			//		'name'=>'cid',
			//		'type'=>'integer',
			//		'default'=>null,
			//		'>'=>0,
			//	]
			//]);
			$v5=$this->filter([
				'category_id'=>[
					'query',
					'name'=>'cid',
					'integer',
					'default',
					'>0',
					'remove',
				]
			]);
			//echo 'v1-v5:', "\n";
			//var_export($v1);
			//echo "\nsame:";
			//var_export(($v1 == $v2) && ($v2 == $v3) && ($v3 == $v4) && ($v4 == $v5));

			echo "\n", 'v5:', "\n";
			var_export($v5);
			echo "\n", 'v6:', "\n";
			$v6=$this->filter('category_id', [
				'query',
				'name'=>'cid',
				'int',
				'default',
				'>0',
				'remove',
			], ['empty'=>['throw'=>404]]);
			var_export($v6);

			//$v7=$this->filter('category_id', [
			//	'source'=>[
			//		'cid'=>[
			//			1,2,3,
			//		],
			//		'cid2'=>[
			//			[1,2,3],
			//			[4,5,6],
			//			[7,8,9],
			//		],
			//		'cid3'=>[
			//			'a'=>[1,2,3],
			//			'b'=>[4,5,6],
			//			'c'=>[7,8,9],
			//		],
			//	],
			//	//'query',
			//	'name'=>'cid3',
			//	'arr'=>[
			//		//'int', '>0',
			//		'arr'=>['int', '>0']
			//	],
			//	'default',
			//]);
			//var_dump($v7);

			//$v8=$this->filter('category_id', [
			//	'source'=>[
			//		'category_id'=>"[123,456]",
			//	],
			//	//'query',
			//	//'name'=>'j',
			//	'json'=>[
			//		'arr'=>['int', '>'=>200],
			//	],
			//	'default',
			//]);
			//var_dump($v8);

			//$v9=$this->filter([
			//	'string'=>[
			//		'len'=>['='=>9, '>'=>3, '<'=>10],
			//		'number',
			//	],
			//	'string2'=>[
			//		'len'=>['='=>9, '>'=>3, '<'=>10],
			//		'pcre'=>'#^\d+#'
			//	],
			//	'email'=>[
			//		'len'=>['<'=>100],
			//		'email',
			//	],
			//	'mobile'=>[
			//		'china-mobile',
			//	],
			//	'id'=>[
			//		'china-id',
			//	],
			//	'hex'=>[
			//		'hex'
			//	],
			//], [
			//	'source'=>[
			//		'string'=>"123456789",
			//		'string2'=>"12345678x",
			//		'email'=>'vea.urn2@gmail.com',
			//		'mobile'=>17090084418,
			//		'id'=>'000000198103230000',
			//		'hex'=>'0xFFFF',
			//	],
			//]);
			//var_dump($v9);
			//var_dump(json_decode('[123,456]x', true));

			//var_dump(date('Y/m-d',strtotime('29.2.2019')));
			//var_dump(date('Y/m-d',strtotime('-1')));
			//var_dump(date('Y/m-d',strtotime('+1')));

			//$v9=$this->filter([
			//	//'id'=>[
			//	//	'callback'=>[
			//	//		'value'=>function($value, callable $error, array $source, string $key){
			//	//			//$error('验证出错', 300, '\Exception');
			//	//			//$error('验证出错');
			//	//			return $source[$key];
			//	//		},
			//	//		//'throw'=>400,
			//	//	],
			//	//],
			//	//'id2'=>[
			//	//	'callback'=>function($value, callable $error, array $source, string $key){
			//	//		return $source[$key];
			//	//	},
			//	//	'name'=>'id'
			//	//],
			//	'id'=>function($value, callable $error, array $source, string $key){
			//		return $value;
			//	},
			//], [
			//	'source'=>[
			//		'string'=>"123456789",
			//		'string2'=>"12345678x",
			//		'email'=>'vea.urn2@gmail.com',
			//		'mobile'=>17090084418,
			//		'id'=>'000000198103230000',
			//		'hex'=>'0xFFFF',
			//	],
			//]);
			//$v10 =$this->filter('id', ['callback'=>function($value, callable $error, $source, string $key){
			//	return $value;
			//}, 'source'=>[
			//	'string'=>"123456789",
			//	'string2'=>"12345678x",
			//	'email'=>'vea.urn2@gmail.com',
			//	'mobile'=>17090084418,
			//	'id'=>'000000198103230000',
			//	'hex'=>'0xFFFF',
			//]]);
			//$v11 =$this->filter('id', function($value, callable $error, $source, string $key){
			//	return $value;
			//});
			//var_dump($v9, $v10, $v11);


		} catch(\Exception $e){
			echo "has error:\n";
			echo "code: ", $e->getCode(), "\n";
			echo "message: ", $e->getMessage(), "\n";
		}

		//var_dump($this->filter(['category_id'=>['query', 'default', '>0', 'name'=>'cid']]));

	}
}

app::factory([
	'filter/throw'=>[
		'throw'=>444,
		//'message'=>[
		//	'unknown'=>'未知规则错误: {rule}',
		//	'from'=>'custorm from 无法找到指定来源: {from}[{name}]',
		//	'default'=>'来源为空: {from}[{name}]',
		//	'>'=>'{from}[{name}]值不大于{check}',
		//	'<'=>'{from}[{name}]值不小于{check}',
		//	'>='=>'{from}[{name}]值小于{check}',
		//	'<='=>'{from}[{name}]值大于{check}',
		//	'array'=>'无效的数组值{from}[{name}]',
		//	'json'=>'无效的json值{from}[{name}]',
		//	'source'=>'错误的数据来源',
		//	'length'=>'{from}[{name}]值长度不正确',
		//	'length='=>'{from}[{name}]值长度不为{check}',
		//	'length<'=>'{from}[{name}]值长度多于{check}',
		//	'length>'=>'{from}[{name}]值长度少于{check}',
		//	'number'=>'{from}[{name}]无效的数字格式',
		//	'pcre'=>'{from}[{name}]无效的内容，未通过验证',
		//	'email'=>'{from}[{name}]无效的邮箱格式',
		//	'url'=>'{from}[{name}]无效的地址格式',
		//	'china-mobile'=>'{from}[{name}]无效的手机号码格式',
		//	'china-id'=>'{from}[{name}]无效的身份证号格式',
		//	'callback'=>'{from}[{name}]无效内容无法通过自定义检测',
		//  'empty'=>'无效的参数值，值为空',
		//]
	]
])->run();

