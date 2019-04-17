<?php
namespace nx\validator;

/**
 * Class valid
 * @package nx\validator
 * @deprecated 2019-03-29
 */
trait valid{
	/**
	 * 返回一个验证器，add()添加规则，start()开始验证并返回内容
	 * @param string $from
	 * @param array $name
	 * @param array $msg
	 * @return \nx\validator\rules
	 */
	public function valid($from='POST', $name=[], $msg=[]){
		return new rules($from, $name, $msg);
	}
}