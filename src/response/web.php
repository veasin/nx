<?php
namespace nx\response;

trait web{
	/**
	 * autoload
	 */
	protected function nx_response_web(){
		$it = is_a($this, 'nx\app') ?$this :$this->app;
		$it->response = new \nx\response();
	}
	/**
	 * @param string $uri
	 * @param string $info
	 * @param int $second
	 * @param bool $die
	 * @return mixed
	 */
	public function redirect($uri = '', $info = 'redirect[%s] now ...', $second = 0, $die = true){
		return $this->response->redirect($uri, $second, $info, $die);
	}
	/**
	 * @param $number
	 * @param string $info
	 * @param bool $die
	 * @return mixed
	 */
	public function status($number, $info = '', $die = true){
		return $this->response->status($number, $info, $die);
	}
}