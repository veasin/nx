<?php
namespace nx\response;

trait view{
	/**
	 * @var \nx\mvc\view
	 */
	//public $response = null;

	protected function nx_response_view()
	{
		$it =is_a($this, 'nx\mvc\controller') ?$this->app :$this;
		$this->response = $this->view('', $it->response->data());
		//$this->response =new \nx\mvc\view([], (isset($this->app) ?$this->app->path :$this->path).'/views/');
	}

	/**
	 * @param string $file
	 * @param array $data
	 * @return view
	 */
	public function view($file = '', $data = [])
	{
		return new \nx\mvc\view($file, $data, (isset($this->app) ? $this->app->path : $this->path) . '/views/');
	}
}