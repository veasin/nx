<?php
namespace nx\mvc;

/**
 * Class view
 * @package nx\mvc
 * @deprecated 2019-02-28
 */
class view extends \nx\response{
	static $_cls = __CLASS__;
	static $_err = null;
	protected $path ='';
	public function __construct($data = []){
		$n =func_num_args();
		if(is_string($data) && $n>1){
			$this->data =func_get_arg(1);
			$this->data['_file_']=$data;
			$this->path =func_get_arg(2);
		} else{
			$this->data =$data;
			$n >1 && $this->path =func_get_arg(1);
		}
		$this->_hasSet =true;
	}
	public function __toString(){ return $this->_hasSet ?$this->render() :''; }
	public function setFile($file = ''){ $this->data['_file_'] = $file; }
	public function render(){
		if(!is_array($this->data)) return $this->data;
		elseif(!array_key_exists('_file_', $this->data)) return json_encode($this->data, JSON_UNESCAPED_UNICODE);
		$_f = $this->data['_file_'];
		try{
			$f = $this->path.$_f.'.php';
			if(!$f) return 'no template file.';
			//\nx\env::get()->logs()->debug('view[{type}] render({file})', ['type'=>__CLASS__, 'file'=>$f]);

			//-------------begin
			$views = [];
			$data = [];
			foreach($this->data as $_key => $_value){
				if($_value instanceof view){
					$views[] = $_key;
				}else $data[$_key] = $_value;
			}
			foreach($views as $_key) $this->data[$_key]->_inherit_ = $data;

			$at = false;
			if(is_null(self::$_err)){
				self::$_err = error_reporting();
				error_reporting(self::$_err || E_NOTICE);
				$at = true;
			}
			if(isset($this->data['_inherit_'])){
				extract($this->data['_inherit_'], EXTR_REFS);
				unset($this->data['_inherit_']);
			}
			//-------------end
			extract($this->data, EXTR_REFS);
			ob_start();
			include($f);
			if($at) error_reporting(self::$_err);
			$r = ob_get_contents();
			ob_end_clean();
			return $r;
		}catch(Exception $e){
			return 'no template file.';
		}
	}
}