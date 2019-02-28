<?php
namespace nx;

/**
 * Class app
 * @package nx
 *
 * @method log($any, $template =false) 输出日志
 * @method main(array $route) 执行默认控制方法
 * @method string i18n() 返回对应语言文本
 * @method array|string|null config(string $word, $params=null) 读取配置
 * @method filter($value, $def=null, $filter=null, ...$pattern) 根据filter来对$value进行过滤，默认返回$def
 * @method \PDO db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method int|false insertSQL($sql, array $params=[], $config='default') 执行插入数据动作 ->insertSQL('INSERT INTO cds (`interpret`, `titel`) VALUES (?, ?)', ['veas', 'new cd']);
 * @method array|false selectSQL($sql, array $params=[], $config='default') 执行查询数据方法 ->selectSQL('SELECT `cds`.* FROM `cds` WHERE `cds`.`id` = ?', [13])
 * @method false|int executeSQL($sql,array $params=[], $config='default') 执行默认控制方法 ->executeSQL('UPDATE `cds` SET `interpret` =? WHERE `cds`.`id` = ?', ['vea', 14])
 * @method \nx\db\table\sql table($name, $primary='id', $config='default') 返回一个sql对象
 * @method request() 返回全部输入内容
 * @method response(array|string $string) 设置默认输出方法
 * @method in() 返回全部输入内容
 * @method out(array|string $string) 设置默认输出方法
 */
class app{
	/**
	 * @var /nx/app 静态实例;
	 */
	static public $instance=null;
	/**
	 * @var \nx\request 请求对象
	 */
	public $request=null;
	/**
	 * @var \nx\response 响应对象
	 */
	public $response=null;
	/**
	 * @var array 预定义缓存存储
	 */
	public $buffer=[];
	/**
	 * @var array 应用设定
	 */
	public $setup=[];
	/**
	 * @var string 工作路径
	 */
	public $path='';
	protected $traits=[];
	/**
	 * @var \nx\input
	 */
	public $in =null;
	/**
	 * @var \nx\output
	 */
	public $out =null;
	/**
	 * 构建app
	 * app constructor.
	 * @param array $setup 传入应用的配置 如数据库 路由 缓存等
	 * @param array $setup     传入应用的配置 如数据库 路由 缓存等
	 * @param array $overwrite 可选重写对象
	 */
	public function __construct($setup=[], $overwrite=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');
		//静态实例
		static::$instance=$this;
		$this->setup =array_merge($this->setup ??[] , $setup ??[]);
		$this->uuid=$setup['uuid'] ?? $this->uuid ?? str_pad(strrev(base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36).base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36)), 6, '0', STR_PAD_RIGHT);
		if($this->ver ?? 1.0 >1.5){
			$this->in =$overwrite['in'] ?? new input();
			$this->out =$overwrite['out'] ?? new output();
		} else {
			$this->request=$overwrite['request'] ?? new request([]);//初始化请求
			$this->response=$overwrite['response'] ?? new response();//初始化相应
			$this->in =&$this->request;
			$this->out =&$this->response;
		}
		//初始化traits
		foreach(class_uses($this) as $_trait){
			$_method=str_replace('\\', '_', $_trait);
			if(method_exists($this, $_method)){
				$r =call_user_func([$this, $_method]);
				if(is_iterable($r)){
					$r->current();
					$this->traits[] =$r;
				}
			}
		}
	}
	/**
	 * @var string 唯一id
	 */
	private $uuid=null;
	public function getUUID(){
		return $this->uuid;
	}
	/**
	 * @var string 唯一id
	 */
	private $real_path=null;
	public function getPath(string $subPath=null){
		if(is_null($this->real_path)) $this->real_path=realpath($this->path ?? dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR;
		return $this->real_path.($subPath ?? '');
	}
	/**
	 * 结束脚本
	 */
	public function __destruct(){
		$this->out =null;//先让response失效，优先输出
		foreach(array_reverse($this->traits) as $trait){
			$trait->next();
		}
	}
	/**
	 * 魔术方法
	 * @param string $name 调用函数名
	 * @param array  $args 调用参数数组
	 * @return mixed
	 */
	public function __call($name, $args){
		switch($name){
			case 'log':
				break;
			case 'main':
				die('need app->main().');
				break;
			case 'router':
				return $this->control(404);
				break;
			case 'control':
				return call_user_func_array($args[0] ?? [$this, 'main'], $args);
				break;
			case 'i18n':
				return $args[0];
				break;
			case 'config':
				return isset($this->config[$args[0]]) ?$this->config[$args[0]] :(isset($args[1]) ?$args[1] :null);
			case 'view':
			case 'status':
				return $this->response->status(...$args);
			case 'filter':
				$this->log('filter:'.json_encode($args));
				return $args[0] ?? $args[1];
			case 'db':
			case 'insertSQL':
			case 'selectSQL':
			case 'executeSQL':
				die('need [trait nx\db\pdo].');
			case 'table':
				die('need [trait nx\db\table].');
			case 'request':
			case 'response':
			case 'in':
			case 'out':
				return ($this->$name)(...$args);
			default:
				die('nothing for ['.$name.'].');
		}
	}
	/**
	 * 创建一个实例
	 * @param array $setup
	 * @param array $overwrite
	 * @return static
	 */
	static public function factory($setup=[], $overwrite=[]){
		return new static($setup, $overwrite);
	}
	/**
	 * 执行应用
	 * @param array ...$route
	 */
	public function run(...$route){
		return $this->control(...$route);
	}
}