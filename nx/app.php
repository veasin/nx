<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2018/08/22 022
 * Time: 17:03
 * https://www.one-tab.com/page/R9L-dMCDQzGWNi04BfNlPg
 * https://www.one-tab.com/page/R9L-dMCDQzGWNi04BfNlPg?action=delete
 *        ___  ___
 *     __ \  \/  /
 *    /  \ \  \ /
 *   /  / \ \  \
 *  /  /\  / \  \
 * /__/  \/__/\__\
 */
declare(strict_types=1);
namespace nx;

/**
 * Class app [psr-11 DI]
 * @package nx
 * @method \Psr\Container\ContainerInterface container($name) 返回指定容器
 * @method mixed in($name=null, ...$name2=null) 返回输入内容
 * @method mixed out($name=null, ...$name2=null) 返回输出内容
 * @method \Psr\Log\LoggerInterface logger(string $name) 返回指定日志对象
 */
class app{
	/**
	 * @var static 静态实例;
	 */
	static public $instance=null;
	/**
	 * @var \nx\input 请求对象
	 */
	public $in=null;
	/**
	 * @var \nx\output 响应对象
	 */
	public $out=null;
	/**
	 * @var \nx\logger nx日志对象
	 */
	public $logger=null;
	/**
	 * @var \nx\container
	 */
	public $container=null;
	/**
	 * @var \nx\router\route;
	 */
	public $router=null;
	/**
	 * @var array 预定义缓存存储
	 */
	//protected $buffer=[];
	/**
	 * @var array 应用设定
	 */
	protected $setup=[];
	/**
	 * @var string 工作路径
	 */
	public $path=null;
	/**
	 * @var array 引入trait列表
	 */
	private $traits=[];
	/**
	 * @var string 唯一id
	 */
	public $uuid='nx';
	/**
	 * @var array 直接缓存结果 config key
	 */
	protected $config=[];
	/**
	 * 构建app
	 * app constructor.
	 * @param array $setup     传入应用的配置 如数据库 路由 缓存等
	 * @param array $overwrite 可选重写对象 in out logger
	 */
	public function __construct($setup=[], $overwrite=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');
		//静态实例
		static::$instance=$this;
		//实例id
		$this->uuid=str_pad(strrev(base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36).base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36)), 6, '0', STR_PAD_RIGHT);
		//设定工作目录
		$this->path=realpath($this->path ?? dirname($_SERVER['SCRIPT_FILENAME'])).'/';
		//读取默认setup配置
		//if(is_array($setup)) $setup =new container\arr(array_merge($this->setup, $setup));
		//创建默认容器
		//$this->container =$overwrite['container'] ?? new container\container(['setup'=>[$setup]]+($setup->get('ci')??[]));
		$this->container=$overwrite['container'] ?? new container($setup);
		//初始化请求
		$this->in=$overwrite['in'] ?? new input();
		//初始化响应
		$this->out=$overwrite['out'] ?? new output();
		//创建日志对象
		$this->logger=$overwrite['logger'] ?? new logger($this->container->get('logger') ?? []);
		//创建路由
		$this->router=$overwrite['router'] ?? $this->container->create('router');
		//初始化traits
		$this->_initTraits(array_map(function($_trait){//初始化trait
			$_method=str_replace('\\', '_', $_trait);
			return method_exists($this, $_method) ?$_method :false;
		}, class_uses($this)));
	}
	/**
	 * @param array $traits 初始化trait，执行初始化方法
	 */
	private function _initTraits($traits){
		$_traits=[];
		foreach($traits as $_trait=>$_method){
			$_depend=$_method ?$this->$_method() :false;
			$this->traits[$_trait]=$_depend ?false :true;
			if($_depend) $_traits[$_trait]=$_method;
		}
		if(!empty($_traits)) $this->_initTraits($_traits);
	}
	/**
	 * 结束脚本
	 */
	public function __destruct(){
		$this->out=null;//先让out失效，优先输出
		$this->logger=null;
	}
	/**
	 * 魔术方法
	 * @param string $name 调用函数名
	 * @param array  $args 调用参数数组
	 * @return mixed
	 */
	public function __call($name, $args){
		switch($name){
			case 'main':
				die('need app->main().');
				break;
			case 'router':
				return $this->control(404);
				break;
			case 'i18n':
				return $args[0];
				break;
			case 'config':
				return isset($this->config[$args[0]]) ?$this->config[$args[0]] :(isset($args[1]) ?$args[1] :null);
			case 'filter':
				return $args[0] ?? $args[1];
			case 'db':
				die('need [trait nx\db\pdo].');
			case 'table':
				die('need [trait nx\db\table].');
			case 'get':
				return $this->container->get($name) ?? $args[0];
			case 'log':
				return ($this->logger)(...$args);
			case 'logger':
			case 'container':
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
	 * @return static
	 */
	static public function factory($setup=[]){
		return new static($setup);
	}
	/**
	 * @param callable|null  $call
	 * @param mixed ...$args
	 * @return mixed
	 */
	private function control($call=null, ...$args){
		return call_user_func_array($call ?? [$this, 'main'], $args);
	}
	/**
	 * 执行应用
	 * @param array ...$route
	 */
	public function run(...$route){
		return $this->control(...$route);
	}
}