<?php
namespace nx\mvc;

/**
 * Class controller
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
class controller{
	static public $instance =null;

	const doExt = 'on';
	const doBefore = 'before';
	const doAfter = 'after';
	/**
	 *
	 * @var \nx\app
	 */
	public $app;
	/**
	 * @var array
	 */
	public $route = [];
	/**
	 * @var \nx\request
	 */
	public $request=null;
	/**
	 * @var \nx\response
	 */
	public $response =null;
	public function __construct($route, $app){
		$this->app = &$app;
		$this->route = $route;

		static::$instance =$this;

		//init use trait
		foreach(class_uses($this) as $_trait){
			$_method =str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)) $this->$_method();
		}
		//load from app
		if(is_null($this->response)) $this->response =&$this->app->response;
		if(is_null($this->request)) $this->request =&$this->app->request;
	}
	public function __get($name){
		$this->$name =&$this->app->$name;
		return $this->$name;
	}
	public function __call($name, $args){
		switch($name){
			case 'no_found':
				return $this->response->status(404);
			default:
				return call_user_func_array([$this->app, $name], $args);
		}
	}
	/**
	 * @param $name
	 * @param bool|false $hook
	 * @param bool|false $all
	 * @return bool
	 */
	public function exec($name, $hook = false, $all =false){
		if($hook){
			$found =false;
			$methods =$all
				?[static::doBefore, static::doBefore.$name, $this->request->method().$name, static::doExt.$name, static::doAfter.$name, static::doAfter]
				:[static::doBefore, $this->request->method(), static::doExt, static::doAfter];
			$r =false;
			foreach($methods as $_fun){
				if(method_exists($this, $_fun)){
					$found =true;
					$r =call_user_func_array([$this,$_fun], $this->route[2]);
					if($r !==null) break;
				}
			}
			if($found ===false) return $this->no_found($name);
			return $r;
		}else if(method_exists($this, $name)) return call_user_func_array([$this,$name], $this->route[2]);
	}
}

