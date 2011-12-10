<?php
class MaintainerHelper extends AppHelper {
	var $helpers = array('Sanction.Clearance');

	function delete($id, $name = null) {
		$name = (!$name) ? $id : $name;

		return $this->Clearance->link(
			sprintf(__('Delete %s'), __('Maintainer')), 
			array(
				'action' => 'delete',
				$id), 
			null,
			sprintf(__('Are you sure you want to delete # %s?'),
			$name));
	}

	function edit($id, $name = null) {
		$name = (!$name) ? __('Maintainer') : $name;

		return $this->Clearance->link(sprintf(__('Edit %s'), $name), array(
			'action' => 'edit',
			$id));
	}
}
?>