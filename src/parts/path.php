<?php
namespace nx\parts;

/**
 * @property-read \nx\helpers\buffer $buffer;
 */
trait path{
	public function getPath(?string $subPath):string{
		if(!isset($this->buffer['path'])){
			$this->buffer['path']=[
				'real'=>realpath($this->path ?? dirname($_SERVER['SCRIPT_FILENAME'])).DIRECTORY_SEPARATOR,
			];
		}
		return $this->buffer['path']['real'].($subPath ?? '');
	}
}