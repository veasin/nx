<?php
namespace nx\helpers;

class random{
	const NUMBER ='0123456789';
	const CHARS ='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_+`-={}|:"<>?[];./,';
	const LETTER ='abcdefghijklmnopqrstuvwxyz';
	const READ ='bcdfghjklmnprstvwxzaeiou';
	/**
	 * 返回随机字符
	 * @param $len
	 * @param string $chars
	 * @return string
	 */
	static public function char($len, $chars=self::CHARS){
		$r = '';
		mt_srand((double)microtime()*1000000);
		$_len =strlen($chars)-1;
		for($i = 0; $i<$len; $i++) $r .= $chars[mt_rand(0, $_len)];
		return $r;
	}
	/**
	 * 返回随机字母
	 * @param $len
	 * @param bool|false $case
	 * @return string
	 */
	static public function letter($len, $case =false){
		return static::char($len, $case ?self::LETTER.strtoupper(self::LETTER) :self::LETTER);
	}
	/**
	 * 返回可读单词
	 * @param $len
	 * @return string
	 */
	static public function word($len){
		$char=self::READ;
		$r = '';
		mt_srand((double)microtime()*1000000);
		for($i = 0; $i<$len; $i++) $r .=($i%2) ?$char[mt_rand(19, 23)]:$char[mt_rand(0, 18)];
		return $r;
	}
	/**
	 * 返回指定长度的数字串
	 * @param $len
	 * @return string
	 */
	static public function number($len){
		return self::char($len, self::NUMBER);
	}
	/**
	 * pseudo v4
	 * @return string
	 */
	static public function uuid(){
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}