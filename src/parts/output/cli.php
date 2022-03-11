<?php
namespace nx\parts\output;

use nx\helpers\output;

trait cli{
	public ?output $out=null;
	public function render_cli(\nx\helpers\output $out, callable $callback=null):void{
		$r=$out();
		if(null !== $callback) $callback($r);else echo $r;
	}
	protected function nx_parts_output_cli():?\Generator{
		$this->out=new output();
		$this->out->setRender([$this, "render_cli"]);
		yield;
		$this->out=null;
	}
}