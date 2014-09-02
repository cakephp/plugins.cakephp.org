<?php
App::uses('Sanitize', 'Utility');

class Maintainer extends AppModel {

/**
 * Name of the model.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#name-1068
 */
	public $name = 'Maintainer';

/**
 * Custom display field name. Display fields are used by Scaffold, in SELECT boxes' OPTION elements.
 *
 * @var string
 * @link http://book.cakephp.org/view/1057/Model-Attributes#displayField-1062
 */
	public $displayField = 'username';

/**
 * Detailed list of belongsTo associations.
 *
 * @var array
 * @link http://book.cakephp.org/2.0/en/models/associations-linking-models-together.html#belongsto
 */
	public $belongsTo = array('User');

/**
 * Detailed list of hasMany associations.
 *
 * @var array
 * @link http://book.cakephp.org/view/1043/hasMany
 */
	public $hasMany = array('Package');

	public $_Github = null;

/**
 * Override the constructor to provide custom model finds
 * and validation rule internationalization
 *
 * @param mixed $id Set this ID for this model on startup, can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->order = "`{$this->alias}`.`{$this->displayField}` asc";
		$this->validate = array(
			'username' => array(
				'alphanumeric' => array(
					'rule' => '/[\w_-]+/i',
					'message' => __('must contain only letters and numbers'),
				),
				'isUnique' => array(
					'rule' => array('isUnique'),
					'message' => __('must be a unique maintainer'),
				),
				'required' => array(
					'rule' => array('notempty'),
					'message' => __('cannot be left empty'),
				),
			),
			'twitter_username' => array(
				'alphanumeric' => array(
					'rule' => array('alphanumeric'),
					'message' => __('must contain only letters and numbers'),
					'allowEmpty' => true,
				),
			),
		);
		$this->findMethods['existing'] = true;
		$this->findMethods['index'] = true;
		$this->findMethods['redirect'] = true;
		$this->findMethods['username'] = true;
		$this->findMethods['user'] = true;
		$this->findMethods['view'] = true;
	}

/**
 * Finds a given maintainer by name as well as their packages
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findExisting($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Nonexistent user'));
			}

			$query['contain'] = array('Package');
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Nonexistent user'));
		}
		return $results[0];
	}

/**
 * Finds maintainers for pagination
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findIndex($state, $query, $results = array()) {
		if ($state == 'before') {
			$query['fields'] = array('id', 'username', 'name', 'alias', 'url', 'twitter_username', 'company', 'location', 'gravatar_id');
			$query['contain'] = false;
			if (!empty($query['operation'])) {
				return $query;
			}
			return $query;
		}

		return $results;
	}

	public function _findRedirect($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query['username'])) {
				throw new InvalidArgumentException(__('Invalid find params'));
			}

			$query['conditions'] = array(
				"{$this->alias}.{$this->displayField}" => $query['username'],
			);
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid package'));
		}

		return $results[0];
	}

/**
 * Finds the current user for the dashboard
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findUser($state, $query, $results = array()) {
		if ($state == 'before') {
			$user_id = false;
			if (!empty($query[0])) {
				$user_id = $query[0];
			} elseif (!empty($query['id'])){
				$user_id = $query['id'];
			} else {
				$user_id = AuthComponent::user('id');
			}

			if (empty($user_id)) {
				throw new InvalidArgumentException(__('Invalid maintainer'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $user_id);
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid user'));
		}
		return $results[0];
	}

/**
 * Finds a user by username
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findUsername($state, $query, $results = array()) {
		if ($state == 'before') {
			if (empty($query[0])) {
				throw new InvalidArgumentException(__('Invalid maintainer'));
			}

			$query['contain'] = false;
			$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid maintainer'));
		}
		return $results[0];
	}

/**
 * Finds a user by name for the /maintainers/view action
 *
 * @param string $state Either "before" or "after"
 * @param array $query
 * @return mixed array of results or false if none found
 * @return array
 */
	public function _findView($state, $query, $results = array()) {
		if ($state == 'before') {
			if (!empty($query[0])) {
				$query['conditions'] = array("{$this->alias}.{$this->displayField}" => $query[0]);
			} else if (!empty($query['maintainer_id'])) {
				$query['conditions'] = array("{$this->alias}.{$this->primaryKey}" => $query['maintainer_id']);
			} else {
				throw new InvalidArgumentException(__('Invalid maintainer'));
			}

			$query['fields'] = array('id', 'username', 'name', 'alias', 'url', 'twitter_username', 'company', 'location', 'gravatar_id', 'avatar_url');
			$query['contain'] = array(
				'Package' => array(
					'Category',
					'conditions' => array('Package.deleted' => 0),
					'order' => array('Package.watchers desc'),
					'fields' => array(
						$this->Package->primaryKey, 'maintainer_id',
						'name', 'description', 'last_pushed_at', 'watchers'
					),
				)
			);
			$query['limit'] = 1;
			return $query;
		}

		if (empty($results[0])) {
			throw new NotFoundException(__('Invalid maintainer'));
		}

		$url = $results[0]['Maintainer']['url'];
		if (strlen($url) && !strpos($url, '://')) {
			$results[0]['Maintainer']['url'] = 'http://' . $url;
		}

		$results[0][$this->alias]['has_summary'] = false;
		$summaryFields = array('company', 'location', 'url', 'twitter_username');
		foreach ($summaryFields as $field) {
			if (!empty($results[0][$this->alias][$field])) {
				$results[0][$this->alias]['has_summary'] = true;
				break;
			}
		}

		if ($results[0][$this->alias]['has_summary']) {
			$results[0][$this->alias]['package_count'] = count($results[0]['Package']);
		}

		if (!empty($results[0]['Package'])) {
			foreach ($results[0]['Package'] as $i => $result) {
				$results[0]['Package'][$i]['description'] = trim($results[0]['Package'][$i]['description']);
				if (empty($results[0]['Package'][$i]['description'])) {
					$results[0]['Package'][$i]['description'] = 'No description available';
				}

				$results[0]['Package'][$i]['Category']['color'] = '';
				if (!empty($results[0]['Package'][$i]['Category']['slug'])) {
					$results[0]['Package'][$i]['Category']['color'] = $this->Package->packageColor($results[0]['Package'][$i]['Category']['slug']);
				}
			}
		}

		if (empty($results[0][$this->alias]['avatar_url'])) {
			try {
				$this->enqueue('UpdateMaintainerJob', array($results[0][$this->alias]['username']));
			} catch (Exception $e) {}
		}

		return $results[0];
	}

/**
 * Ensure the uri is valid if it's been specified
 *
 * @return void
 */
	public function beforeSave($options = array()) {
		if (!empty($this->data[$this->alias]['url'])) {
			if (!strpos($this->data[$this->alias]['url'], '://')) {
				$this->data[$this->alias]['url'] = 'http://' . $this->data[$this->alias]['url'];
			}
		}
		return true;
	}

/**
 * Returns SEO for a maintainer
 *
 * @param array $maintainer
 * @return array
 */
	public function seoView($maintainer) {
		if ($maintainer['Maintainer']['name']) {
			$name = $maintainer['Maintainer']['name'];
		} else {
			$name = $maintainer['Maintainer']['username'];
		}

		$title = array();
		$title[] = Sanitize::clean($name);
		$title[] = 'CakePHP Package Maintainer';
		$title[] = 'CakePackages';
		$title = implode(' | ', $title);

		$description = Sanitize::clean($name) . ' - CakePHP Package on CakePackages';

		$keywords = array();
		if (!empty($maintainer['Package'])) {
			$keywords = array_slice(Set::classicExtract($maintainer, 'Package.{n}.name'), 0, 5);
		}
		$keywords[] = 'cakephp package';
		$keywords[] = 'cakephp';
		$keywords = implode(' | ', $keywords);

		return array($title, $description, $keywords);
	}

	public function retrieveMaintainerData($username) {
		if (!$this->_Github) {
			$this->_Github = ClassRegistry::init('Github');
		}

		$user = $this->_Github->find('user', array('user' => $username));
		if (empty($user)) {
			return false;
		}

		$data = array(
			'github_id'   => Hash::get($user, 'User.id', ''),
			'username'    => Hash::get($user, 'User.login', ''),
			'gravatar_id' => Hash::get($user, 'User.gravatar_id', ''),
			'avatar_url'  => Hash::get($user, 'User.avatar_url', ''),
			'name'        => Hash::get($user, 'User.name', ''),
			'company'     => Hash::get($user, 'User.company', ''),
			'name'        => Hash::get($user, 'User.name', ''),
			'url'         => Hash::get($user, 'User.blog', ''),
			'email'       => Hash::get($user, 'User.email', ''),
			'location'    => Hash::get($user, 'User.location', ''),
		);

		foreach (array_keys($data) as $key) {
			if (empty($data[$key])) {
				unset($data[$key]);
			}
		}

		return $data;
	}

	public function updateExistingMaintainer($username) {
		$maintainer = $this->findByUsername($username);
		if (empty($maintainer)) {
			return false;
		}

		$data = $this->retrieveMaintainerData($username);
		if (empty($data)) {
			return false;
		}

		$maintainer['Maintainer'] = array_merge($maintainer['Maintainer'], $data);
		return $this->save($maintainer);
	}
}
