<?php
namespace nx\response;

trait view{
	/**
	 * @var \nx\mvc\view
	 */
	//public $response = null;
	protected function nx_response_view(){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		//$it->response=$this->view('', $it->response->data());
		$it->response=$this->view();
		//$this->response =new \nx\mvc\view([], (isset($this->app) ?$this->app->path :$this->path).'/views/');
	}
	/**
	 * @param string $file
	 * @param array $data
	 * @return view
	 */
	public function view($file='', $data=[]){
		$it=is_a($this, 'nx\app') ?$this :$this->app;
		return new \nx\mvc\view($file, $data, $it->path.'/views/');
	}
}