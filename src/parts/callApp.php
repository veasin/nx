<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/12/18 018
 * Time: 13:44
 */
namespace nx\parts;

/**
 * Trait callApp
 * @package nx\base
 * @method log($any, $template=false) 输出日志
 * @method runtime($any, string $from) 输出运行日志
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method \nx\helpers\db\pdo db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method in() 返回全部输入内容
 * @method out(array|string $string) 设置默认输出方法
 * @property \nx\helpers\input in 输入对象
 * @property \nx\helpers\output out 输出对象
 * @property \nx\helpers\buffer buffer 缓存
 * @property string uuid 唯一id
 * @method string getPath(string $subPath) 获取相对app的目录
 * @method throw($codeOrException=400, $message='', $exception='\Exception') 抛出指定异常
 * @method filter(array|string $vars=[], array $options=[]) 过滤器，对输入进行过滤。可指定输入内容来源或设置来源数组。
 */
trait callApp{
	/**
	 * 魔术方法，属性转发到app上 $this->something
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name): mixed{
		return \nx\app::$instance->$name;
		//$this->$name=&\nx\app::$instance->$name;
		//return $this->$name;
	}
	/**
	 * 魔术方法，所有未知方法调用都转发到app上 $this->something()
	 * @param string $name
	 * @param array  $args
	 * @return mixed
	 */
	public function __call(string $name, array $args){
		return \nx\app::$instance->{$name}(...$args);
	}
}