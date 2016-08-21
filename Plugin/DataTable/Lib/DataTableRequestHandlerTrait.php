<?php
trait DataTableRequestHandlerTrait {

	public function processDataTableRequest() {
		$config = $this->request->query('config');
		if (method_exists($this, $config)) {
			$this->setAction($config);
			return;
		}
		$this->DataTable->paginate($config);
	}
}