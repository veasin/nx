<?php
include '../vendor/autoload.php';
const AGREE_LICENSE=true;
date_default_timezone_set('Asia/Shanghai');

class app_rest extends nx\app{
	use \nx\parts\output\rest, \nx\parts\log\file;
	protected ?string $path=__DIR__;
	public function main():void{
		$this->in->registerContentTypeParse('xxx', fn($input)=>explode(',', $input));
		$this->out['ok']=true;
		$this->out['method'] =$this->in['method'];
		$this->out['body'] =$this->in['body'];
	}
}
(new app_rest())->run();