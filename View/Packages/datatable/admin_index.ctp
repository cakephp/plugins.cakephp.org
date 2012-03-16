<?php
foreach($dtResults as $result) {
	extract($result['Package']);

	$name = $this->Html->link($name, array('action' => 'edit', $id));
	if (!empty($repository_url)) {
		$username = $this->Html->link(
			$result['Maintainer']['username'],
			$repository_url,
			array('target' => '_blank')
		);
	} else {
		$username = $result['Maintainer']['username'];
	}
	$enabled = empty($deleted) ? 'Yes' : 'No';
	$created = $this->Time->format('Y-m-d', $created);
	$last_pushed_at = $this->Time->format('Y-m-d', $last_pushed_at);

	$actions = $this->Html->link(__('Edit'), array('action' => 'edit', $id)) . '&nbsp;|&nbsp;';
	if (empty($deleted)) {
		$label = __('Disable');
	} else {
		$label = __('Enable');
	}
	$actions .= $this->Html->link(
		$label,
		array('action' => 'disable', $id),
		array(),
		'Are you sure you want to ' . strtolower($label) . ' package #' . $id . '?'
	);
	
	$this->dtResponse['aaData'][] = array(
		$id,
		$name,
		$username,
		$created,
		$last_pushed_at,
		$enabled,
		$actions,
	);
}