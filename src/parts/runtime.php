<?php

namespace nx\parts;
/**
 * todo 是否需要接管log？ 这样可以方便确认log所在进程位置
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
        //var_dump("\n".implode("\n", $list)."\n");
    }
    protected function log_runtime_writer($log): void{
        $v = \nx\helpers\log::interpolate($log['message'], $log['context']);
        $r ="[{$log['level']}] $v";
        if('runtime' !==$log['level'] && !empty($log['trace'])) $r .="  <- {$log['trace']['file']}:{$log['trace']['line']}";
        $this->runtime($r, 'log');
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
        $_from =$from ? "".str_pad($from, 5, ' ', STR_PAD_LEFT).'|' : '';
        $fileline ='';
        if($backtrace){
            $dbt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 15);
            array_shift($dbt);
            array_shift($dbt);
            $trace =[];
            $i =0;
            foreach($dbt as $dbto){
                $class =$dbto['class'] ?? '';
                $type =$dbto['type'] ?? '';
                $function =$dbto['function'] ?? '';
                $file =$dbto['file'] ?? '';
                $line =$dbto['line'] ?? '';
                if(str_starts_with($class, 'nx\\') || ($file ==='' || $line === '') || ($class ==='') ) continue;
                $trace[] ="$class$type$function() ";
                if(0===$i) $fileline =" <- $file:$line";
                $i ++;
                if($i>2) break;
            }
            $trace =" # ".implode(" < ", $trace)."";
        } else $trace ='';
        $w =str_pad(" $step$_from $var", 120);
        $this['runtime:list'][] = "$w $fileline $trace";
    }
}
