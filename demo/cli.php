#!/usr/bin/env php
<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2017/06/29 029
 * Time: 17:27
 */
define('AGREE_LICENSE', true);
include __DIR__."/../src/autoload.php";
\nx\autoload::register([]);

class app extends \nx\app{
	use \nx\router\cli,
		\nx\control\main,
		\nx\log\file;
	public $path =__DIR__;

	function main($args){
		/**
		 * do you what want
		 */
		var_dump($args);

	}
}
app::factory()->args("f:v::abc", [
	"required:",     // Required value
	"optional::",    // Optional value
	"option",        // No value
	"opt",           // No value
])->run();
