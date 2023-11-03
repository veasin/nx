<?php
include '../vendor/autoload.php';
const AGREE_LICENSE=true;
date_default_timezone_set('Asia/Shanghai');

class app_cli extends nx\app{
	use \nx\parts\output\cli;
	protected ?string $path=__DIR__;
	public function main():void{
		$this->out('is ok.');
		//$this->xxx();
	}
}
$app =new app_cli();
$app->run();
//var_dump($app);
