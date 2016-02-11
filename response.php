<?php
namespace nx;

use nx\log\header;

class response extends o2{
	private $_http_status = [
		200 => "OK",
		301 => "Moved Permanently",
		304 => "Not Modified",
		401 => "Unauthorized",
		403 => "Forbidden",
		404 => "Not Found",
	];
	public function redirect($uri = '', $second = 3, $info = 'redirect[%s] now ...', $die=true){
		if(empty($uri))
			$uri = $_SERVER['REQUEST_URI'];
		if($second == 0){
			header('Location: ' . $uri);
			if($die) die();
		} else{
			header('Refresh: ' . $second . '; url=' . $uri);
			if($die) die($this->i18n($info, $uri));
		}
	}
	public function status($number, $info = '', $die =true){
		$info = isset($this->_http_status[$number])
			?$this->_http_status[$number]
			:$info;
		$status ='HTTP/1.1 ' . $number . ' ' . $info;
		header($status);
		if($die) die($status);
		return $this;
	}
	/**
	 * @param string $type
	 *              application/json
	 *              application/atom+xml
	 *              application/rss+xml
	 *              application/zip
	 *              text/css
	 *              text/javascript
	 *              text/plain
	 *              text/xml
	 *              image/jpeg
	 *              audio/mpeg
	 * @param string|bool $charset
	 *              utf-8
	 *              ISO-8859-1
	 * @return $this
	 */
	public function contentType($type='text/html', $charset=false){
		$header ='Content-Type: '.$type;
		if($charset) $header.=';charset='.$charset;
		header($header);
		return $this;
	}
	public function language($lng='zh_cn'){
		header('Content-Language: '.$lng);
		return $this;
	}
	public function noCache(){
		header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
		header('Pragma: no-cache');
		return $this;
	}
	public function lastModified($time=false, $noModifiedExit=true){
		if(empty($time)) $time = time() - 60; // or filemtime($fn), etc
		$timeC =gmdate('D, d M Y H:i:s', $time).' GMT';
		if($noModifiedExit && isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && $timeC ==$_SERVER['HTTP_IF_MODIFIED_SINCE']){
			$this->status(304);
		}
		header('Last-Modified: '.$timeC);
		return $this;
	}
	public function poweredBy($by='nx'){
		header('X-Powered-By: '.$by);
		return $this;
	}
	public function attachment($file, $displayName=null){
		if (is_file($file) && file_exists($file)){
			header('Content-length: ' . filesize($file));
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename=' . (empty($displayName) ? basename($file) :$displayName) . '');
			readfile($file);
		} else{
			$this->status(404);
		}
	}
	public function etag($etag, $noModifiedExit=true){
		if($noModifiedExit && isset($_SERVER['HTTP_IF_NONE_MATCH']) && $etag==$_SERVER['HTTP_IF_NONE_MATCH']){
			$this->status(304);
		}
		header('ETag: '.$etag);
	}
}