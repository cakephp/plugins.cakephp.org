<?php
class UserDetail extends AppModel {

/**
 * Detailed list of belongsTo associations.
 *
 * @var array
 */
	public $belongsTo = array(
		'User',
	);

/**
 * Displayfield
 *
 * @var string
 */
	public $displayField = 'field';

/**
 * order
 *
 * @var array
 */
	public $order = array('UserDetail.position ASC');

/**
 * List of valid finder method options, supplied as the first parameter to find().
 *
 * @var array
 */
	public $findMethods = array(
		'detail' => true,
		'sections' => true,
	);

/**
 * Validation rules for the virtual fields for each section
 *
 * @var array
 */
	public $sectionValidation = array();

/**
 * Used to declare field types for each section to make validation work on the virtual fields
 *
 * @var array
 */
	public $sectionSchema = array();

/**
 * Create the default fields for a user
 *
 * @param string $userId User ID
 * @return void
 */
	public function createDefaults($userId) {
		$entries = array(
			array(
				'field' => 'user.firstname',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.middlename',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.lastname',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.abbr-country-name',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.abbr-region',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.country-name',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.location',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.postal-code',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.region',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			),
			array(
				'field' => 'user.timeoffset',
				'value' => '',
				'input' => 'text',
				'data_type' => 'string',
			)
		);

		$i = 0;
		$data = array();
		foreach ($entries as $entry) {
			$data[$this->alias] = $entry;
			$data[$this->alias]['user_id'] = $userId;
			$data[$this->alias]['position'] = $i++;
			$this->create();
			$this->save($data);
		}
	}

	public function _findSections($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['conditions'] = array(
				"{$this->alias}.user_id" => $query['userId'],
			);

			if (!is_null($query['section'])) {
				$query['conditions']["{$this->alias}.field LIKE"] = $query['section'] . '.%';
			}

			$query['fields'] = array("{$this->alias}.field", "{$this->alias}.value");
			$query['recursive'] = -1;
			return $query;
		}

		return $results;
	}

	public function _findDetail($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['userId']) || empty($query['field'])) {
				throw new InvalidArgumentException(__('Invalid detail'));
			}

			$query['conditions'] = array(
				"{$this->alias}.user_id" => $query['userId'],
				"{$this->alias}.field" => $query['field'],
			);

			$query['fields'] = array("{$this->alias}.{$this->primaryKey}", "{$this->alias}.field");
			$query['limit'] = 1;
			$query['recursive'] = -1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('The detail does not exist'));
		}
		return $results[0];
	}

/**
 * Returns details for named section
 *
 * @var string $userId User ID
 * @var string $section Section name
 * @return array
 */
	public function getSection($userId = null, $section = null) {
		$results = $this->find('sections', compact('userId', 'section'));

		if (!empty($results)) {
			foreach($results as $result) {
				list($prefix, $field) = explode('.', $result[$this->alias]['field']);
				$details[$prefix][$field] = $result[$this->alias]['value'];
			}
			$results = $details;
		}
		return $results;
	}

/**
 * Save details for named section
 *
 * @var string $userId User ID
 * @var array $data Data
 * @var string $section Section name
 * @return boolean True on successful validation and saving of the virtual fields
 */
	public function saveSection($userId = null, $data = null, $section = null) {
		if (!empty($this->sectionSchema[$section])) {
			$tmpSchema = $this->_schema;
			$this->_schema = $this->sectionSchema[$section];
		}

		if (!empty($this->sectionValidation[$section])) {
			$tmpValidate = $this->validate;
			$data = $this->set($data);
			$this->validate = $this->sectionValidation[$section];
			if (!$this->validates()) {
				return false;
			}
			$this->validate = $tmpValidate;
		}

		if (isset($tmpSchema)) {
			$this->_schema = $tmpSchema;
		}

		if (!empty($data) && is_array($data)) {
			foreach($data as $model => $details) {
				if ($model == $this->alias) {
					// Save the details
					foreach($details as $key => $value) {
						// Quickfix for date inputs - TODO Try to use $this->deconstruct()?
						if (is_array($value) && array_keys($value) == array('month', 'day', 'year')) {
							$value = $value['year'] . '-' . $value['month'] . '-' .  $value['day'];
						}
						$newDetail = array();
						$field = $section . '.' . $key;
 						try {
							$detail = $this->find('detail', compact('userId', 'field'));
							$this->create();
							$newDetail[$model]['id'] = $detail[$this->alias]['id'];
						} catch (Exception $e) {
							$newDetail[$model]['user_id'] = $userId;
						}

						$newDetail[$model]['field'] = $field;
						$newDetail[$model]['value'] = $value;
						$newDetail[$model]['input'] = '';
						$newDetail[$model]['data_type'] = '';
						$newDetail[$model]['label'] = '';
						$this->save($newDetail, false);
					}
				} elseif (isset($this->{$model})) {
					// Save other model data
					$toSave[$model] = $details;
					if (!empty($userId)) {
						if ($model == 'User') {
							$toSave[$model]['id'] = $userId;
						} elseif ($this->{$model}->hasField('user_id')) {
							$toSave[$model]['user_id'] = $userId;
						}
					}
					$this->{$model}->save($toSave, false);
				}
			}
		}
		return true;
	}
}
