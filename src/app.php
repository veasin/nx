<?php
namespace nx;

use Error;
use Exception;
use Throwable;
use nx\helpers\buffer;

/**
 * Class app
 * @package nx
 *
 * @method log($any, $template=false) 输出日志
 * @method main(array $route) 执行默认控制方法
 * @method string i18n() 返回对应语言文本
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method helpers\db\pdo db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method @deprecated request() 返回全部输入内容
 * @method @deprecated response(array|string $string) 设置默认输出方法
 * @property \nx\helpers\input in
 * @property \nx\helpers\output out
 * @method in() 返回全部输入内容
 * @method out(array|string $string) 设置默认输出方法
 * @method throw($codeOrException=400, $message='', $exception=Exception::class) 抛出指定异常
 * @method filter(array|string $vars=[], array $options=[]) 过滤器，对输入进行过滤。可指定输入内容来源或设置来源数组。
 * @method filterValue(mixed $value, array $options=[]) 过滤器，针对值做过滤，不包含取值逻辑。
 * @method control(mixed $route)
 */
class app{
	/**
	 * @var static 静态实例;
	 */
	public static app $instance;
	/**
	 * @var string 唯一id
	 */
	public readonly string $uuid;
	/**
	 * @var array 应用配置
	 */
	public readonly array $setup;
	/**
	 * @var \nx\helpers\buffer 应用缓存 运行时
	 */
	protected readonly buffer $buffer;
	/**
	 * @var array trait 引用
	 */
	protected array $traits=[];
	/**
	 * 构建app
	 * app constructor.
	 * @param array $setup     传入应用的配置 如数据库 路由 缓存等
	 * @param array $overwrite 可选重写对象
	 * @throws \Exception
	 */
	public function __construct(array $setup=[], array $overwrite=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');
		//静态实例
		static::$instance=$this;
		$this->uuid=$overwrite['uuid'] ?? bin2hex(random_bytes(3));
		$this->setup=[...($this->setup ?? []), ...$setup];
		$this->buffer=new buffer();
		//初始化traits
		foreach(class_uses($this) as $_trait){
			$_method=str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)){
				$r=$this->$_method($overwrite);
				if(is_iterable($r)){
					$r->current();
					$this->traits[]=$r;
				}
			}
		}
	}
	/**
	 * 结束脚本
	 */
	public function __destruct(){
		foreach(array_reverse($this->traits) as $trait){
			$trait->next();
		}
	}
	/**
	 * 魔术方法
	 * @param string $name 调用函数名
	 * @param array  $args 调用参数数组
	 * @return mixed|null|void
	 * @throws \Throwable
	 */
	public function __call(string $name, array $args){
		switch($name){
			case 'throw':
				if($args[0] instanceof Throwable) throw $args[0];
				$exp=$args[2] ?? '\Exception';
				$msg=$args[1] ?? '';
				throw new $exp($msg, $args[0]);
			case 'log':
				return;
			case 'router':
				return $this->control(404);
			case 'control':
				return ($args[0] ?? false) ?call_user_func_array($args[0], $args) :$this->main(...$args);
			case 'config':
				return $this->setup[$args[0]] ?? ($args[1] ?? null);
			case 'in':
			case 'out':
				if(!property_exists($this, $name)){
					//$io="nx\helpers\\{$name}put";
					//$this->{$name}=new $io();
					throw new Error("Call to undefined method ".static::class."->$name(), u maybe need a nx\parts\\{$name}put.");
				}
				return ($this->$name)(...$args);
		}
		throw new Error("Call to undefined method ".static::class."->$name(), u maybe need nx\parts\\$name.");
	}
	public function __get($name){
		switch($name){
			case 'in':
			case 'out':
				if(!property_exists($this, $name)){
					$io="nx\helpers\\{$name}put";
					$this->{$name}=new $io();
				}
				return $this->$name;
		}
		return null;
	}
	/**
	 * 执行应用
	 * @param array ...$route
	 * @return mixed
	 */
	public function run(...$route):mixed{
		return $this->control(...$route);
	}
}