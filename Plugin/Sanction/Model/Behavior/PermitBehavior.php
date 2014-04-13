<?php

App::uses('Hash', 'Utility');
App::uses('Inflector', 'Utility');
App::uses('UnauthorizedException', 'Error');

/**
 * Permit behavior class
 *
 * Manages user access to a model record
 *
 * @package       Sanction
 * @subpackage    Sanction.Controller.Component
 */
class PermitBehavior extends ModelBehavior {

/**
 * Initiate behavior for the model using specified settings.
 *
 * Available settings:
 *
 * - message: (string, optional) A message to display to the user when they do not
 *   have access to the model record. DEFAULTS TO: "You do not have permission
 *   to view this %ModelAlias%"
 * - check: (string, optional) optional admin override for returning the results
 *   when the user_id does not match the current user. DEFAULTS TO: false
 * - value: (mixed, optional) The value the check should resolve to. DEFAULTS TO: true
 * - field: (string, optional) A `Hash::get()`-compatible string for retrieving the
 *   from the current record user_id.  DEFAULTS TO: %ModelAlias%.user_id
 * - skip: (boolean, optional) Whether to skip rule checking. DEFAULTS TO: true
 * - rules: (array, optional) If `permit` is set in a `Model::find()`, this key will
 *   be used to make an index lookup for rules to apply to this find. DEFAULTS TO: empty array
 *
 * @param Model $Model Model using the behavior
 * @param array $settings Settings to override for model.
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = array(
				'message' => sprintf('You do not have permission to view this %s ',
					strtolower(Inflector::humanize($Model->alias))
				),
				'check' => false,
				'value' => true,
				'field' => $Model->alias . '.user_id',
				'skip' => true,
				'rules' => array(),
			);
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $settings);
		$this->modelDefaults[$Model->alias] = $this->settings[$Model->alias];
	}

/**
 * beforeFind Callback
 *
 * @param Model $model Model find is being run on.
 * @param array $query Array of Query parameters.
 * @return array Modified query
 */
	public function beforeFind(Model $Model, $query) {
		$this->settings[$Model->alias] = $this->modelDefaults[$Model->alias];

		// check if $this->modelDefaultsPersist has been set
		if (isset($this->modelDefaultsPersist[$Model->alias])) {
			// if persist equals equals true
			if (!isset($this->modelDefaults[$Model->alias]['persist']) || $this->modelDefaults[$Model->alias]['persist'] == false) {
				$this->modelDefaults[$Model->alias] = $this->modelDefaultsPersist[$Model->alias];
			}
		}

		if (isset($query['permit']) && isset($this->settings[$Model->alias]['rules'][$query['permit']])) {
			$rules = $this->settings[$Model->alias]['rules'][$query['permit']];
			if (isset($rules['rules'])) {
				unset($rules['rules']);
			}

			$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], $rules);
		}

		foreach (array('message', 'check', 'value', 'field', 'skip', 'rules') as $key) {
			if (isset($query['permit_' . $key])) {
				$this->settings[$Model->alias][$key] = $query['permit_' . $key];
			}
		}

		return $query;
	}

/**
 * afterFind Callback
 *
 * @param Model $model Model find was run on
 * @param array $results Array of model results.
 * @param boolean $primary Did the find originate on $model.
 * @return array Modified results
 * @throws UnauthorizedException
 */
	public function afterFind(Model $Model, $results, $primary) {
		if (!$primary) {
			return $results;
		}

		$settings = $this->settings[$Model->alias];

		if ($settings['skip'] === true) {
			return $results;
		}

		// the permit behavour is a bit pointless if we're handing more than one result
		if (count($results) > 1) {
			return $results;
		}

		// HACK: Retrieve the zeroth index in the resultset
		$userId = Hash::get($results, "0.{$settings['field']}");
		if ($userId === null) {
			return $results;
		}

		if ($userId == $this->user($Model, $results, 'id')) {
			return $results;
		}

		$adminCheck = $settings['check'];
		$adminValue = $settings['value'];
		if ($adminCheck && $this->user($Model, $results, $adminCheck) == $adminValue) {
			return $results;
		}

		throw new UnauthorizedException($settings['message']);
	}

/**
 * Wrapper around retrieving user data
 *
 * Can be overriden in the Model to provide advanced control
 *
 * @param array $result single Model record being authenticated against
 * @param string $key field to retrieve.  Leave null to get entire User record
 * @return mixed User record. or null if no user is logged in.
 */
	public function user(Model $Model, $result, $key = null) {
		if (method_exists($Model, 'user')) {
			return $Model->user($key, $result);
		}

		if (class_exists('AuthComponent')) {
			return AuthComponent::user($key);
		}

		if (class_exists('Authsome')) {
			return Authsome::get($key);
		}

		if (method_exists($Model, 'get')) {
			$className = get_class($Model);
			$ref = new ReflectionMethod($className, 'get');
			if ($ref->isStatic()) {
				return $className::get($key);
			}
		}

		return false;
	}

/**
 * Used to dynamically assign permit settings
 *
 * @param array $settings same as the settings used to set-up the model, with the addition of 'persist' (boolean), which will keep the passed settings for all future model calls
 * @return void
 */
	public function permit(Model $Model, $settings = array()) {
		// store existing model defaults
		$this->modelDefaultsPersist[$Model->alias] = $this->modelDefaults[$Model->alias];
		// assign new settings
		$this->modelDefaults[$Model->alias] = array_merge($this->modelDefaults[$Model->alias], $settings);
	}

}
