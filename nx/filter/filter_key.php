<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/1/4 004
 * Time: 09:03
 */
namespace nx\filter;

interface filter_key{
	public function filter(...$rules);
}