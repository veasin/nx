<?php
namespace nx\log;

trait file{

	protected function nx_log_file(){
		$setup =isset($this->setup['log.file']) ?$this->setup['log.file'] :[];
		$name =date(isset($setup['name']) ?$setup['name'] :'Y-m-d');
		$line =isset($setup['line']) ?$setup['line'] :'[var]';
		$file =$this->path.'/logs/'.$name.'.log';
		$this->buffer['log_file'] =[
			'file' =>$file,
			'line' =>$line,
			'handle' =>fopen($file, 'a'),
		];

		$this->log(' -- '.$_SERVER['REQUEST_TIME'].':['.$_SERVER['REQUEST_METHOD'].']'.$_SERVER['REQUEST_URI'].'', true);
	}

	public function log($var, $onlyvar =false){
		if(empty($this->buffer['log_file']['handle'])) return ;

		if($onlyvar) return fwrite($this->buffer['log_file']['handle'], $var."\n");

		$line =str_replace([
			'{var}',
			'{time}',
			'{datetime}',
			'{app}',
		], [
			(string)$var,
			date('H:i:s'),
			date('Y-m-d H:i:s'),
			__CLASS__,
		], $this->buffer['log_file']['line']);

		fwrite($this->buffer['log_file']['handle'], $line."\n");
	}

}
