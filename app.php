<?php
namespace nx;

/**
 * Class app
 * @package nx
 */
class app{
	static public $instance=null;
	/**
	 * @var \nx\request
	 */
	public $request=null;
	/**
	 * @var \nx\o2
	 */
	public $response =null;
	public $buffer=[];
	protected $setup=[];
	public $path='';
	private $traits =[];
	public function __construct($setup=[]){
		header(__NAMESPACE__.':vea 2005-2016');
		(defined('AGREE_LICENSE') && AGREE_LICENSE ===true) || die('thx use nx(from github[urn2/nx]), need AGREE_LICENSE !');

		static::$instance=$this;
		if(!empty($setup)) $this->setup=array_merge($this->setup, $setup);

		if($this->path=='') $this->path=dirname($_SERVER['SCRIPT_FILENAME']);

		$this->request=new request();

		//init use trait
		$this->initTraits(array_map(function($_trait){
			$_method =str_replace('\\', '_', $_trait);
			return method_exists($this, $_method) ?$_method :false;
		}, class_uses($this)));

		if(is_null($this->response)) $this->response =new o2();
		//$this->response['app'] =get_class($this);
	}
	private function initTraits($traits){
		$this->buffer['traits'] =[];
		foreach($traits as $_trait =>$_method){
			$_depend =$_method ?$this->$_method() :false;
			$this->traits[$_trait] =$_depend ? false :true;
			if($_depend) $this->buffer['traits'][$_trait] =$_method;
		}
		if(!empty($this->buffer['traits'])) $this->initTraits($this->buffer['traits']);
	}
	public function __destruct(){
		$this->log("end.\n");
		//header_remove('X-Powered-By');
		echo $this->response;
	}
	public function __get($offset){
		switch($offset){
			case 'response':
				$this->$offset=new o2();
				break;
		}
		return $this->$offset;
	}
	public function __call($name, $args){
		switch($name){
			case 'log':
				//call_user_func_array('var_dump', $args);
				break;
			case 'router':
				return $this->control(404);
				break;
			case 'control':
				header('HTTP/1.0 404 Not Found');
				die();
				break;
			case 'i18n':
				return $args[0];
				break;
			case 'config':
				return isset($this->config[$args[0]])
					?$this->config[$args[0]]
					:(isset($args[1])
						?$args[1]
						:null);
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
	static public function factory($setup=[]){
		return new static($setup);
	}
	public function run($route=null){
		if(func_num_args()==0) $this->router();
		else $this->control($route);
	}
}