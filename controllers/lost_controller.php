<?php
class LostController extends AppController {
	var $name = 'Lost';
	var $uses = array();

	function index() {
		$arguments = '';
		foreach ($this->params['pass'] as $argument) {
			$arguments .= $argument;
		}
		$url = (substr($this->here, 0, 5) == '/lost') ? substr($this->here, 5) : $this->here;
		$url = Router::normalize($url);
		$this->header("HTTP/1.0 404 Not Found");
		$this->set(array(
			'code' => '404',
			'name' => __('Not Found', true),
			'message' => h($url),
			'base' => $this->base
		));
		CakeLog::write('info', serialize($arguments));
		$this->autoRender = $this->autoLayout = true;
		return $this->render();
	}
}
?>