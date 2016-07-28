<?php
namespace nx\config;

/**
 * Trait files
 * @trait app
 * @package nx\config
 */
trait files{
	protected function nx_config_files(){
		!isset($this->config) && $this->config =[];
		if(isset($this->buffer)){
			if(!isset($this->buffer['config'])) $this->buffer['config'] =[];
		}
	}

	public function config($word, $params =null){
		if(isset($this->config[$word])) return $this->config[$word];

		$_ns =$word;
		$_key =null;
		if(strpos($word, '.') !== false) list($_ns, $_key) =explode('.', $word, 2);

		$buffer =&$this->buffer['config'];
		if(!isset($buffer[$_ns])){
			$config = [];
			if(is_file($file =$this->path.'/config/'.$_ns.'.php')){
				$config =include($file);
			}
			$buffer[$_ns] =$config;
		}

		$_config =$params;
		if(isset($buffer[$_ns])){
			if(is_null($_key)) $_config =$buffer[$_ns];
			elseif(isset($buffer[$_ns][$_key])) $_config =$buffer[$_ns][$_key];
		}

		$this->config[$word] =$_config;
		return $_config;
	}


}