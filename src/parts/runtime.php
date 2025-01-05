<?php

namespace nx\parts;
/**
 * @property mixed|null $log
 */
trait runtime{
	/**
	 * @return \Generator|null
	 * @throws \JsonException
	 */
	protected function nx_parts_runtime(): ?\Generator{
		$this['runtime:ms'] = microtime(true);
		$this['runtime:list']=[];
		$this->log?->addWriter($this->log_runtime_writer(...),'runtime');//todo 添加file的过滤，屏蔽各种类型的日志
		$this->runtime(($_SERVER['REQUEST_METHOD'] ?? 'CLI').' '.($_SERVER['REQUEST_URI'] ?? $_SERVER['SCRIPT_FILENAME']), '>');
		yield;
		$this->runtime("end.", '>');
		$this->log?->log('runtime',"\n".implode("\n", $this['runtime:list'])."\n");
	}
	protected function log_runtime_writer($log): void{
		$v = \nx\helpers\log::interpolate($log['message'], $log['context']);
		if('runtime'!==$log['level']) $this->runtime("{$log['level']}: $v", 'log', true);
	}
	public function runtime($var, $from='', $backtrace =false): void{
		if(in_array($from, $this['runtime:hide'] ?? [])) return ; //todo 替换成 setup 配置: 1 毫秒时间 2 来源from 3 过滤显示
		if(!is_string($var)){
			try{
				$var = json_encode($var, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
			}catch(\JsonException){
				$var = "Error Json Format.";
			}
		}
		$step = sprintf("%05.2f", (microtime(true) - $this['runtime:ms']) * 1000);
		$_from =$from ? str_pad($from, 5, ' ', STR_PAD_LEFT).'|' : '';
		$file_line ='';
		if($backtrace){
			$dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
			//var_dump($var, $dbt);
			array_shift($dbt);
			array_shift($dbt);
			array_shift($dbt);
			$trace =[];
			$i =0;
			$path = dirname(__DIR__, 5) .DIRECTORY_SEPARATOR;

			foreach($dbt as $_trace){
				//$class =$_trace['class'] ?? '';
				//$type =$_trace['type'] ?? '';
				//$function =$_trace['function'] ?? '';
				$file =$_trace['file'] ?? '';
				$line =$_trace['line'] ?? '';
				if($file) $file =str_replace([$path, "\\"], ["", '/'], $file);
				if(!$file || !$line //|| !$class
					|| str_starts_with($file, 'vendor/')
				) continue;
				$trace[] ="$file:$line ";
				$i ++;
				if($i>5) break;
			}
			if(count($trace)){
				$trace =" # ".implode(" < ", $trace);
			} else $trace ='';
		} else $trace ='';
		$var =trim($var);
		$this['runtime:list'][] = " $step$_from $var$file_line $trace";
	}
}
