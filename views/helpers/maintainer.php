<?php
class MaintainerHelper extends AppHelper {
	var $helpers = array('Html');

	function delete($id, $name = null) {
		$name = (!$name) ? $id : $name;

		return $this->Html->link(
			sprintf(__('Delete %s', true), __('Maintainer', true)), 
			array(
				'action' => 'delete',
				$id), 
			null,
			sprintf(__('Are you sure you want to delete # %s?', true),
			$name));
	}

	function edit($id, $name = null) {
		$name = (!$name) ? __('Maintainer', true) : $name;

		return $this->Html->link(sprintf(__('Edit %s', true), $name), array(
			'action' => 'edit',
			$id));
	}
}
?>