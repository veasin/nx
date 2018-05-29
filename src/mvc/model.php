<?php
namespace nx\mvc;

/**
 * Class model
 * @package nx\mvc
 *
 * @method log($any, $template =false) 输出日志
 * @method main(array $route) 执行默认控制方法
 * @method router() 执行路由方法
 * @method string i18n() 返回对应语言文本
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method \nx\response\view view($file='', $data=[]) 返回模板试图
 * @method filter($value, $def=null, $filter=null, ...$pattern) 根据filter来对$value进行过滤，默认返回$def
 * @method \PDO db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method int|false insertSQL($sql, array $params=[], $config='default') 执行插入数据动作 ->insertSQL('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
 * @method array|false selectSQL($sql, array $params=[], $config='default') 执行查询数据方法 ->selectSQL('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
 * @method false|int executeSQL($sql,array $params=[], $config='default') 执行默认控制方法 ->executeSQL('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
 * @method \nx\db\table\sql table($name, $primary='id', $config='default') 返回一个sql对象
 * @method request() 返回全部输入内容
 * @method response(string $string) 设置默认输出方法
 * @method no_found(string $name) 执行默认不存在方法动作
 */
class model{
	/**
	 * @var \nx\app
	 */
	public $app = null;
	public $buffer =[];
	protected $lastError =[1, '未知错误'];
	public function __construct(...$setup){
		$this->app = \nx\app::$instance;
		//初始化引入的trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}
		//如果本身存在init方法，那么就立刻执行
		if(method_exists($this, 'init')) $this->init(...$setup);
	}
	/**
	 * 实例化model并缓存
	 * @param array ...$setup
	 * @return static
	 */
	static public function instance(...$setup){
		$c = get_called_class();
		return new $c(...$setup);
	}
	/**
	 * 无参数返回最后的错误信息，需要在model中提前指定，否则为设定最后错误编号和注释，同时会写入日志
	 * @see return $this->lastError(1,''未知错误);
	 * @param int    $code 记录日志的错误编码
	 * @param string $message 记录日志的消息内容
	 * @param bool   $return 默认返回值 false
	 * @return array|bool
	 */
	public function lastError($code=0, $message='', $return=false){
		if(func_num_args() >0){
			$this->lastError =[$code, $message];
			$this->log('model error: '.$code.' - '.$message);
			return $return;
		}
		return $this->lastError;
	}
	/**
	 * 魔术方法，所有未知方法调用都转发到app上
	 * @param $name
	 * @param $args
	 * @return mixed
	 */
	public function __call($name, $args){
		return call_user_func_array([$this->app, $name], $args);
	}
}
