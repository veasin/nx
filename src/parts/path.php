<?php
namespace nx\parts;

trait path{
	public function getPath(?string $subPath):string{
		if(!isset($this['app:path'])){
			$this['app:path'] =realpath($this->path ?? $this['path'] ?? dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR;
		}
		return $this['app:path'].($subPath ?? '');
	}
}