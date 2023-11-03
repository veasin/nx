<?php
include '../vendor/autoload.php';
const AGREE_LICENSE=true;
date_default_timezone_set('Asia/Shanghai');

class app_http extends nx\app{
	use \nx\parts\output\http;
	protected ?string $path=__DIR__;
	public function main():void{
		$this->out('ok');
	}
}
(new app_http())->run();
