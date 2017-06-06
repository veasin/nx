<?php
namespace nx\tools;

/**
 * Class valid
 * @package nx\tools
 */
trait valid{
	/**
	 * 返回一个验证器，add()添加规则，start()开始验证并返回内容
	 * @param string $from
	 * @param array $name
	 * @param array $msg
	 * @return \nx\helpers\validator
	 */
	public function valid($from='POST', $name=[], $msg=[]){
		return new \nx\helpers\validator($from, $name, $msg);
	}
}