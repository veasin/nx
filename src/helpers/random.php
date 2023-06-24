<?php
namespace nx\helpers;

class random{
	const NUMBER ='0123456789';
	const CHARS ='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_+`-={}|:"<>?[];./,';
	const LETTER ='abcdefghijklmnopqrstuvwxyz';
	const READ ='bcdfghjklmnprstvwxzaeiou';
	/**
	 * 返回随机字符
	 *
	 * @param int    $len
	 * @param string $chars
	 * @return string
	 */
	static public function char(int $len, string $chars=self::CHARS): string{
		$r = '';
		mt_srand((double)microtime()*1000000);
		$_len =strlen($chars)-1;
		for($i = 0; $i<$len; $i++) $r .= $chars[mt_rand(0, $_len)];
		return $r;
	}
	/**
	 * 返回随机字母
	 *
	 * @param int  $len
	 * @param bool $case
	 * @return string
	 */
	static public function letter(int $len, bool $case =false): string{
		return static::char($len, $case ?self::LETTER.strtoupper(self::LETTER) :self::LETTER);
	}
	/**
	 * 返回可读单词
	 *
	 * @param int $len
	 * @return string
	 */
	static public function word(int $len): string{
		$char=self::READ;
		$r = '';
		mt_srand((double)microtime()*1000000);
		for($i = 0; $i<$len; $i++) $r .=($i%2) ?$char[mt_rand(19, 23)]:$char[mt_rand(0, 18)];
		return $r;
	}
	/**
	 * 返回指定长度的数字串
	 *
	 * @param int $len
	 * @return string
	 */
	static public function number(int $len): string{
		return self::char($len, self::NUMBER);
	}
	/**
	 * pseudo v4
	 * @return string
	 */
	static public function uuid(): string{
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),
			mt_rand(0, 0xffff),
			mt_rand(0, 0x0fff) | 0x4000,
			mt_rand(0, 0x3fff) | 0x8000,
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
		);
	}
}