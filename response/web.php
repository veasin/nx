<?php
namespace nx\response;

trait web{
	/**
	 * @var \nx\mvc\view
	 */
	//public $response = null;
	protected function nx_response_web(){
		$it = is_a($this, 'nx\app') ?$this :$this->app;
		$it->response = new \nx\response();
	}
	public function redirect($uri = '', $second = 3, $info = 'redirect[%s] now ...', $die = true){
		return $this->response->redirect($uri, $second, $info, $die);
	}
	public function status($number, $info = '', $die = true){
		return $this->response->status($number, $info, $die);
	}
}