<?php
class Setting extends SettingsAppModel {
	public $name = 'Setting';

	function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->validate = array(
			'key' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true)
				),
			),
			'title' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true)
				),
			),
			'description' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true)
				),
			),
			'input_type' => array(
				'notempty' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty', true)
				),
			),
		);
	}
}
?>