<?php
namespace nx\parts;
/**
 * Trait callApp
 *
 * @package nx
 * @method log(...$args) 输出日志
 * @property \nx\helpers\log log 日志对象
 * @method runtime($var, $from='', $backtrace =false) 输出运行日志 use runtime
 * @method \nx\helpers\db\pdo db(string $name='default') 根据$app['db/pdo'] 的配置创建pdo对象 use pdo
 * @method \nx\helpers\db\sql table(?string $tableName=null, ?string $primary=null, ?string $config=null) 根据$app['db/pdo'] 的配置创建sql对象 use pdo
 * @property \nx\helpers\input in 输入对象
 * @method in() 返回全部输入内容
 * @property \nx\helpers\output out 输出对象
 * @method out(array|string $string) 输出
 * @method string getPath(string $subPath) 获取相对app的目录 use path
 * @method throw($codeOrException=400, $message='', $exception='\Exception') 抛出指定异常
 * @property \nx\helpers\filter\from filter 过滤对象
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