<?php
namespace nx\config;

/**
 * Trait ini
 * @trait app
 * @package nx\config
 */
trait ini{
	protected function nx_config_ini(){
		$file =$this->path.'/'.(isset($this->setup['config.ini']) ?$this->setup['config.ini'] :'config').'.ini';
		!isset($this->config) && $this->config =[];
		if(isset($this->buffer)){
			//if(!isset($this->buffer['config'])) $this->buffer['config'] =[];
			$this->buffer['config'] =@parse_ini_file($file, true);
			$this->buffer['config']['ini.file'] =$file;
		}
	}

	public function config($word, $params =null){
		if(isset($this->config[$word])) return $this->config[$word];

		$_ns =$word;
		$_key =null;
		if(strpos($word, '.') !== false) list($_ns, $_key) =explode('.', $word, 2);

		$buffer =&$this->buffer['config'];
		$_config =$params;
		if(isset($buffer[$_ns])){
			if(is_null($_key)) $_config =$buffer[$_ns];
			elseif(isset($buffer[$_ns][$_key])) $_config =$buffer[$_ns][$_key];
		}

		$this->config[$word] =$_config;
		return $_config;
	}


}