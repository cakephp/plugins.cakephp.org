<?php
class SearchableBehavior extends ModelBehavior {

/**
 * Default settings
 *
 * @var array
 * @access protected
 */
	var $_defaults = array(
		'fields' => null,
		'scope' => array(),
		'name' => null,
		'summary' => null,
		'published' => null,
		'url' => null,
	);

/**
 * Records being saved or deleted that the Searchable Behavior transfers to
 * the SearchIndex
 *
 * @var array
 * @access protected
 */
	var $_records;

/**
 * Initiate SearchableBehavior
 *
 * @param AppModel $model
 * @param array $config Array of options for configuring the Searchable
 * Behavior settings for the given model. Keys include:
 * - fields - array of fields from the model to include in the search index.
 * If omitted, all char, varchar, string, text fields will be included.
 * To include data from fields in the current model, just specify the field
 * name. E.g. array('title')
 * The Searchable Behavior can include data from associated models in the
 * Search Index too. This is useful for say Post belongsTo Category, and you
 * want the Category name included in the Search Index.
 * To achieve this, specify the field name in the current model as the key and
 * the model and field in the associated model as the value. E.g.
 * array('category_id' => 'Category.name')
 * - scope - array of conditions in the form array('field' => 'value') to
 * apply to determine whether the record in the Search Index is active or not,
 * and therefore whether it should be included in the search results. If
 * omitted, the record in the Search Index is always active.
 * - name - the field to be used from the Searchable model when populating the
 * name field in the Search Index. This is used as the title of a search
 * result entry on the results page, and has the link to view the result on
 * it. If omitted, the displayField of the model is used. Set to false if you
 * don't want the title field to be populated.
 * - summary - the field to be used from the Searchable model when populating
 * the summary field in the Search Index. This is used as the summary of a
 * search result entry on the results page. If omitted, no field is used, and
 * the summary will be a series of excerpts from the Search Index data with
 * the search terms highlighted.
 * - published - the field to be used from the Searchable model when
 * populating the published field in the Search Index. This can be used in the
 * conditions array when performing the search. If omitted, no field is used,
 * and the published field contain NULL.
 * - url - array of url elements e.g. controller, action etc. If controller is
 * omitted, the controller version of the model is used. If action is omitted,
 * view is used, if there are no other non-url paramters (e.g. slug), the
 * Searchable Model primary key value is added to the url.
 */
	function setup(&$model, $config = array()) {
		if (!is_array($config)) {
			$config = array($config);
		}

		// Add config to settings for given model
		$this->settings[$model->alias] = array_merge($this->_defaults, $config);

		// Normalize the fields property using default string types from the model
		// schema if not specified, or processing the fields config param passed in.
		if (empty($this->settings[$model->alias]['fields'])) {
			foreach ($model->schema() as $field => $info) {
				if (in_array($info['type'], array('text','varchar','char','string','date'))) {
					$this->settings[$model->alias]['fields'][$model->alias.'.'.$field] = $model->alias.'.'.$field;
				}
			}
		} else {
			// Ensure fields is in the format array(Model.field => Model.field, ...)
			foreach ($this->settings[$model->alias]['fields'] as $field => $modelField) {
				unset($this->settings[$model->alias]['fields'][$field]);
				if (strpos($modelField, '.') === false) {
					$modelField = $model->alias.'.'.$modelField;
				}
				if (is_numeric($field)) {
					$field = $modelField;
				} elseif (strpos($field, '.') === false) {
					$field = $model->alias.'.'.$field;
				}
				$this->settings[$model->alias]['fields'][$field] = $modelField;
			}
		}

		// Set 'name' to false if you don't want to populate the 'name' field
		if (!isset($this->settings[$model->alias]['name']) || $this->settings[$model->alias]['name'] !== false) {
			$this->settings[$model->alias]['name'] = $model->displayField;
		}

		// If url is not an array, make it one
		if (!isset($this->settings[$model->alias]['url'])) {
			$this->settings[$model->alias]['url'] = array();
		}

		// Add default plugin url component of null
		if (!isset($this->settings[$model->alias]['url']['plugin'])) {
			$this->settings[$model->alias]['url']['plugin'] = null;
		}
		// Add default controller url component of controller version of model
		if (!isset($this->settings[$model->alias]['url']['controller'])) {
			$this->settings[$model->alias]['url']['controller'] = Inflector::pluralize(Inflector::underscore($model->alias));
		}
		// Add default action of view
		if (!isset($this->settings[$model->alias]['url']['action'])) {
			$this->settings[$model->alias]['url']['action'] = 'view';
		}
	}

/**
 * Called automatically after saving a model with Searchable attached.
 *
 * Saves searchable model record being saved's data to Search Index.
 *
 * @param AppModel $model
 * @param boolean $created
 */
	function afterSave(&$model, $created) {
		$this->setSearchableRecords($model, $created);
		$this->saveSearchIndex($model, $created);
	}

/**
 * Remembers records being deleted so corresponding records in Search Index
 * can be deleted in afterDelete
 *
 * @param AppModel $model
 * @return boolean Always true
 */
	function beforeDelete(&$model) {
		$this->setSearchableRecords($model);
		return true;
	}

/**
 * Deletes searchable model record being deleted from Search Index.
 *
 * @param AppModel $model
 */
	function afterDelete(&$model) {
		$this->deleteSearchIndex($model);
	}

/**
 * Creates array of $model->alias => array($model->id => $model->data) for
 * use by afterDelete or afterSave.
 *
 * @param AppModel $model
 * @param boolean $created Indicates whether the record is being/was created
 * @param boolean $reset Determines whether to reset the _records property
 * before adding new ones.
 */
	function setSearchableRecords(&$model, $created = false, $reset = false) {
		if ($reset) {
			$this->_records = array();
		}
		// Set the foreign key from either the model id or the last inserted id
		$foreignKey = $model->id;
		if (!$foreignKey && $created) {
			$foreignKey = $model->getInsertID();
		}
		$this->_records[$model->alias][$foreignKey] = $model->data;
	}

/**
 * Saves the Search Index record for the corresponding Searchable Model record
 *
 * @param AppModel $model
 * @param boolean $created
 */
	function saveSearchIndex(&$model, $created = false) {
		$this->SearchIndex = ClassRegistry::init('Searchable.SearchIndex');

		foreach ($this->_records[$model->alias] as $this->_foreignKey => $this->_modelData) {
			$this->_initialiseSearchIndexData($model, $created);

			if (method_exists($model, 'getSearchableData')) {
				$data = $model->getSearchableData($this->_modelData);
			} else {
				$data = $this->_getSearchableData($model);
			}

			// Merge data with default or existing data, and json_encode it ready for
			// saving the Search Index record.
			$this->SearchIndex->data['SearchIndex']['data'] = array_merge($this->SearchIndex->data['SearchIndex']['data'], $data);
			$new_data = array();
			foreach ($this->SearchIndex->data['SearchIndex']['data'] as $key => $value) {
				$new_data[sha1($key)] = $value;
			}
			$this->SearchIndex->data['SearchIndex']['data'] = $new_data;
			$this->SearchIndex->data['SearchIndex']['data'] = json_encode($this->SearchIndex->data['SearchIndex']['data']);
			$this->_setScope($model, $created);
			$this->_setExtra($model, 'name');
			$this->_setExtra($model, 'summary');
			$this->_setExtra($model, 'published');
			$this->_setUrl($model, $data);

			$this->SearchIndex->save();
		}
	}

/**
 * Enter description here...
 */
	function _setUrl(&$model, $data) {
		$url = $this->settings[$model->alias]['url'];
		$nonStandardUrlComponents = array_diff_key($url, array_flip(array('plugin', 'controller', 'action')));

		if (empty($nonStandardUrlComponents)) {
			$url[] = $this->_foreignKey;
		} else {
			foreach ($nonStandardUrlComponents as $modelName => $modelFields) {
				foreach ($modelFields as $key => $value) {
					unset($url[$key]);
					if (isset($data["{$modelName}.{$value}"])) {
						$value = $data["{$modelName}.{$value}"];
					}

					if (!is_numeric($key)) {
						$url[$key] = $value;
					} else {
						$url[] = $value;
					}
				}
				unset($url[$modelName]);
			}
		}
		$this->SearchIndex->data['SearchIndex']['url'] = json_encode($url);
		return;
	}

/**
 * Sets extra fields in the Search Index data property with values straight
 * from model data (if set). Used for populating the name and summary fields.
 *
 * @param AppModel $model
 * @param string $field
 */
	function _setExtra(&$model, $field) {
		// If the current model is configured to have this field, just go back
		if (!$this->settings[$model->alias][$field]) {
			return;
		}

		// If the field is not set in the model data property, just go back. In this
		// case it will be filled with NULL as that is the default in the DB if the
		// record is being created, or, if an edit, the previous value will be set.
		if (!isset($this->_modelData[$model->alias][$this->settings[$model->alias][$field]])) {
			return;
		}

		// Populate the Search Index data property with the value from model data
		$this->SearchIndex->data['SearchIndex'][$field] = $this->_cleanValue($this->_modelData[$model->alias][$this->settings[$model->alias][$field]]);
	}

/**
 * Sets the active field value in the Search Index data property according
 * to the scope of the Searchable Model record
 *
 * @param AppModel $model
 * @param boolean $created
 */
	function _setScope(&$model, $created) {
		// If the Searchable model doesn't have scope, just go back.
		if (empty($this->settings[$model->alias]['scope'])) {
			return;
		}

		// Check whether the Searchable Model scope has actually been set, i.e. the
		// scope data is available in model data. If it is not and the record has
		// not just been created, do not explicitly set the active field in the
		// Search Index data property - i.e. active will remain unchanged.
		$scopeFieldsInModelData = array_intersect_key($this->_modelData[$model->alias], $this->settings[$model->alias]['scope']);
		if (empty($scopeFieldsInModelData) && !$created) {
			return;
		}

		// Find out the scope of the current record in the Searchable Model by
		// checking whether it meets the scope conditions
		$conditions = $this->settings[$model->alias]['scope'] + array($model->primaryKey => $this->_foreignKey);
		$inScope = $model->hasAny($conditions);

		$this->SearchIndex->data['SearchIndex']['active'] = (int) $inScope;
	}

/**
 * Returns the data extracted from model->data
 *
 * @param AppModel $model
 * @return array Array of <Model>.<field> => <value>
 */
	function _getSearchableData(&$model) {
		$data = array();

		// Iterate through the fields configured to be used in the Search Index data
		// field identifying the Model.field in the model data and the Model.field
		// in the model or associated model data.
		foreach ($this->settings[$model->alias]['fields'] as $modelDataSource => $searchDataSource) {
			list($modelDataAlias, $modelDataField) = explode('.', $modelDataSource);

			// If the Model.field for the source is not in model data, continue
			if (!isset($this->_modelData[$modelDataAlias][$modelDataField])) {
				continue;
			}

			// Get the value from the model data for the given source Model.field
			$value = $this->_modelData[$modelDataAlias][$modelDataField];

			// If the real value to include in the Search Index data field is actually
			// from an associated model, fetch that value
			if ($modelDataSource != $searchDataSource) {
				list($searchDataAlias, $searchDataField) = explode('.', $searchDataSource);

				// The value from the associated model may already be in model data
				if (isset($this->_modelData[$searchDataAlias][$searchDataField])) {
					$value = $this->_modelData[$searchDataAlias][$searchDataField];
				} else { // But if it isn't, fetch it from the database.
					$modelDataValue = $this->_modelData[$modelDataAlias][$modelDataField];
					$AssocModel = ClassRegistry::init($searchDataAlias);
					$AssocModel->id = $modelDataValue;
					$value = $AssocModel->field($searchDataField);
				}
			}
			$data[$searchDataSource] = $this->_cleanValue($value);
		}
		return $data;
	}

/**
 * Initialises Search index model id and data properties.
 *
 * If editing a Searchable Model record, fetch the details of that record from
 * the database, so we can merge over the data field keys with the new model
 * data.
 *
 * @param AppModel $model
 * @param boolean $created
 */
	function _initialiseSearchIndexData(&$model, $created) {
		$this->SearchIndex->create();
		if (!$created) {
			// Try and find an existing Search index record for this model record
			$existing = $this->SearchIndex->find('first', array(
				'conditions' => array(
					'SearchIndex.model' => $model->alias,
					'SearchIndex.foreign_key' => $this->_foreignKey,
					),
				)
			);
			// If found, set the id and data properties of the Search Index model
			if ($existing) {
				$this->SearchIndex->id = $existing['SearchIndex']['id'];
				$this->SearchIndex->data = $existing;
				// Transform the data field back to an array, ready for replacing the
				// values with the new model data
				$this->SearchIndex->data['SearchIndex']['data'] = json_decode($this->SearchIndex->data['SearchIndex']['data'], true);
				return;
			}
		}
		// We are creating, or the corresponding SearchIndex record was not found,
		// so merge the default SearchIndex field values with the details about the
		// current model and initialise the data field as an empty array ready for
		// populating with data from the model.
		$this->SearchIndex->data['SearchIndex'] = array_merge($this->SearchIndex->data['SearchIndex'], array(
			'model' => $model->alias,
			'foreign_key' => $this->_foreignKey,
			'data' => array(),
			)
		);
	}

/**
 * Removes html, trims and converts html entities back to normal text.
 *
 * @param string $value
 * @return string
 */
	function _cleanValue($value) {
		$value = strip_tags($value);
		$value = trim($value);
		$value = html_entity_decode($value, ENT_COMPAT, 'UTF-8');
		return $value;
	}

/**
 * Delete single (identified by $model->id) or all records in the Search Index
 * for a particular model.
 *
 * @param AppModel $model
 * @param boolean $all Whether to delete all records or single
 * @return boolean Result of
 */
	function deleteSearchIndex(&$model, $all = false) {
		$conditions = array('SearchIndex.model' => $model->alias);
		if (!$all) {
			$conditions['SearchIndex.foreign_key'] = $model->id;
		}
		return ClassRegistry::init('Searchable.SearchIndex')->deleteAll($conditions);
	}
}
?>