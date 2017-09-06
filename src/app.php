<?php
namespace nx;

/**
 * Class app
 * @package nx
 */
class app{
	/**
	 * 静态实例
	 * @var /nx/app;
	 */
	static public $instance=null;
	/**
	 * 请求
	 * @var \nx\request
	 */
	public $request=null;
	/**
	 * 响应
	 * @var \nx\response
	 */
	public $response=null;
	/**
	 * 预定义缓存存储
	 * @var array
	 */
	public $buffer=[];
	/**
	 * 应用设定
	 * @var array
	 */
	public $setup=[];
	/**
	 * 工作路径
	 * @var string
	 */
	public $path='';
	/**
	 * 引入trait列表
	 * @var array
	 */
	private $traits=[];
	/**
	 * 唯一id
	 * @var string
	 */
	public $uid='';
	/**
	 * 构建app
	 * app constructor.
	 * @param array $setup 传入应用的配置 如数据库 路由 缓存等
	 */
	public function __construct($setup=[]){
		(defined('AGREE_LICENSE') && AGREE_LICENSE === true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');
		//实例id
		$this->uid=str_pad(strrev(base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36).base_convert(mt_rand(0, 36 ** 3 - 1), 10, 36)), 6, '0', STR_PAD_RIGHT);
		static::$instance=$this;//静态实例
		if(!empty($setup)) $this->setup=array_merge($this->setup, $setup);//合并配置文件
		if($this->path == '') $this->path=dirname($_SERVER['SCRIPT_FILENAME']);//设定工作目录
		$this->request=new request($this);//初始化请求
		$this->response=new response($this);//初始化相应
		$this->_initTraits(array_map(function($_trait){//初始化trait
			$_method=str_replace('\\', '_', $_trait);
			return method_exists($this, $_method) ?$_method :false;
		}, class_uses($this)));
	}
	/**
	 * @param array $traits 初始化trait，执行初始化方法
	 */
	private function _initTraits($traits){
		$this->buffer['traits']=[];
		foreach($traits as $_trait=>$_method){
			$_depend=$_method ?$this->$_method() :false;
			$this->traits[$_trait]=$_depend ?false :true;
			if($_depend) $this->buffer['traits'][$_trait]=$_method;
		}
		if(!empty($this->buffer['traits'])) $this->_initTraits($this->buffer['traits']);
	}
	/**
	 * 结束脚本
	 */
	public function __destruct(){
		$this->log("end.\n");
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
				if(!$this->request['cli']) header('HTTP/1.0 404 Not Found');
				die();
				break;
			case 'i18n':
				return $args[0];
				break;
			case 'config':
				return isset($this->config[$args[0]]) ?$this->config[$args[0]] :(isset($args[1]) ?$args[1] :null);
			case 'view':
			case 'status':
				if(is_callable([$this->response, $name])){
					$this->response->$name($args[0], $args[1]);
				}
				else die("need [trait response->{$name}]");
				break;
			case 'db':
			case 'insertSQL':
			case 'selectSQL':
			case 'executeSQL':
				die('need [trait nx\db\pdo].');
			case 'table':
				die('need [trait nx\db\table].');
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
	 * 执行应用
	 * @param array ...$route
	 */
	public function run(...$route){
		return 0 === count($route) ?$this->router() :$this->control(...$route);
	}
}