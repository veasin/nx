<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/12 012
 * Time: 15:22
 */
namespace nx\helpers\network;

/**
 * contentType
 * Class type
 * @package nx\network
 */
class type{
	const WWW_FORM='application/x-www-form-urlencoded';
	const FORM_DATA='multipart/form-data';
	const JSON='application/json';
	const ATOM='application/atom+xml';
	const RSS='application/rss+xml';
	const ZIP='application/zip';
	const CSS='text/css';
	const JS='text/javascript';
	const TEXT='text/plain';
	const XML='text/xml';
	const HTML='text/html';
	const JPEG='image/jpeg';
	const PNG='image/png';
	const GIF='image/gif';
	const MPG='audio/mpeg';
	const ABBR=[
		'form'=>self::FORM_DATA,
		'json'=>self::JSON,
		'atom'=>self::ATOM,
		'rss'=>self::RSS,
		'zip'=>self::ZIP,
		'css'=>self::CSS,
		'js'=>self::JS,
		'txt'=>self::TEXT,
		'text'=>self::TEXT,
		'xml'=>self::XML,
		'html'=>self::HTML,
		'jpg'=>self::JPEG,
		'jpeg'=>self::JPEG,
		'png'=>self::PNG,
		'gif'=>self::GIF,
		'mpg'=>self::MPG,
		'mpeg'=>self::MPG,
	];
}