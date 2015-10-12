<?php
namespace nx\log;

/**
 * Class header
 * @trait app
 * @package nx\log
 */
trait header{
	static private $_nx_log_num=0;
	protected function nx_log_header(){
		set_error_handler(function($errno, $errstr, $errfile, $errline, $err){
			if(__FILE__ ==$errfile && E_RECOVERABLE_ERROR ==$errno){
				$this->log($err['var'], true);
				return true;
			} else return false;
		});
	}
	public function log($var, $mustEncode =false){
		//var_dump(static::$_nx_log_num, $var, $mustEncode);
		if(is_object($var)){
			$args ='';
			if(!$mustEncode){
				$args .=$var;
				if(!empty($var) && empty($args)) return ;
			}
			$args ='['.get_class($var).']:'.$args;
		} else $args =json_encode($var, JSON_UNESCAPED_UNICODE);

		static::$_nx_log_num +=1;
		header('log-'.static::$_nx_log_num.':'.$args, false);
	}
}