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
 * @method helpers\db\pdo db($name='default') 根据$app->setup['db/pdo'] 的配置创建pdo对象
 * @method @deprecated request() 返回全部输入内容
 * @method @deprecated response(array|string $string) 设置默认输出方法
 * @method in() 返回全部输入内容
 * @method out(array|string $string) 设置默认输出方法
 * @method throw($codeOrException=400, $message='', $exception='\Exception') 抛出指定异常
 * @method filter(array|string $vars=[], array $options=[]) 过滤器，对输入进行过滤。可指定输入内容来源或设置来源数组。
 * @method filterValue(mixed $value, array $options=[]) 过滤器，针对值做过滤，不包含取值逻辑。
 */
class app{
	/**
	 * @var static 静态实例;
	 */
	static public $instance=null;
	/**
	 * @var \nx\buffer 预定义缓存存储
	 */
	public $buffer=null;
	/**
	 * @var array 应用设定
	 * @deprecated 2020-6-23 使用config替换
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
	protected $ver =1.1;
	/**
	 * @var \nx\helpers\ci\setup
	 */
	public $config =null;
	/**
	 * 构建app
	 * app constructor.
	 * @param array $setup     传入应用的配置 如数据库 路由 缓存等
	 * @param array $overwrite 可选重写对象
	 */
	public function __construct($setup=[], $overwrite=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');
		//静态实例
		static::$instance=$this;
		$this->setup =($this->setup ??[]) + ($setup ??[]);
		$this->config =is_a($setup, 'nx\helpers\config\setup') ? $setup :new helpers\config\setup($this->setup);
		$this->buffer =new buffer();
		$this->uuid=$this->config['uuid'] ?? $this->uuid ?? str_pad(strrev(base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36).base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36)), 6, '0', STR_PAD_RIGHT);
		$this->in =$overwrite['in'] ?? new input();
		$this->out =$overwrite['out'] ?? new output();
		if(($this->ver ?? 1.0) ==1.0){
			$this->request=$overwrite['request'] ?? new request([]);//初始化请求
			$this->response=$overwrite['response'] ?? new response();//初始化相应
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
	 * @return mixed|\nx\response|null
	 * @throws \Throwable
	 */
	public function __call($name, $args){
		switch($name){
			case 'throw':
				if($args[0] instanceof \Throwable) throw $args[0];
				$exp =$args[2] ?? '\Exception';
				$msg =$args[1] ?? '';
				throw new $exp($msg, $args[0]);
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
		}
		die('nothing for ['.$name.'].');
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
	 * @return mixed
	 */
	public function run(...$route){
		return $this->control(...$route);
	}
}