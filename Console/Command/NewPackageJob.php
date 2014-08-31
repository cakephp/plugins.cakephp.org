<?php
App::uses('AppShell', 'Console/Command');
App::uses('PackageData', 'Lib');

class NewPackageJob extends AppShell {

	public $uses = array('Maintainer', 'Github');

	public function work() {
		$username = $this->args[0];
		$package_name = $this->args[1];
		$this->out(sprintf('Verifying package uniqueness %s/%s', $username, $package_name));

		$maintainer = $this->getMaintainer($username);
		if (!$maintainer) {
			return false;
		}

		$existing = $this->getExisting($maintainer['Maintainer']['id'], $package_name);
		if ($existing) {
			return false;
		}

		$packageData = new PackageData($username, $package_name, $this->Github);
		$data = $packageData->retrieve();
		if ($data === false) {
			return;
		}

		$data['maintainer_id'] = $maintainer['Maintainer']['id'];

		$this->out('Saving package');
		$this->Maintainer->Package->create();
		$saved = $this->Maintainer->Package->save(array('Package' => $data));

		if (!$saved) {
			return $this->out('Package not saved');
		}

		// $id = $this->Maintainer->Package->getLastInsertID();
		// $package = $this->Maintainer->Package->setupRepository($id);
		// if ($package) {
		// 	$this->Maintainer->Package->characterize($package);
		// }

		$this->out('Package saved');
	}

	public function getMaintainer($username) {
		try {
			$maintainer = $this->Maintainer->find('view', $username);
		} catch (InvalidArgumentException $e) {
			$this->out($e->getMessage());
			return false;
		} catch (NotFoundException $e) {
			$this->out("Maintainer not found, creating...");
			$maintainer = $this->createMaintainer($username);
			if (!$maintainer) {
				$this->out($e->getMessage());
				return false;
			}
		} catch (Exception $e) {
			$this->out('Unable to find maintainer: ' . $e->getMessage());
			return false;
		}

		return $maintainer;
	}

	public function createMaintainer($username) {
		$user = $this->Github->find('user', array('user' => $username));

		$data = array('Maintainer' => array(
			'username'    => (isset($user['User']['login']))       ? $user['User']['login'] : '',
			'gravatar_id' => (isset($user['User']['gravatar_id'])) ? $user['User']['gravatar_id'] : '',
			'name'        => (isset($user['User']['name']))        ? $user['User']['name'] : '',
			'company'     => (isset($user['User']['company']))     ? $user['User']['company'] : '',
			'url'         => (isset($user['User']['blog']))        ? $user['User']['blog'] : '',
			'email'       => (isset($user['User']['email']))       ? $user['User']['email'] : '',
			'location'    => (isset($user['User']['location']))    ? $user['User']['location'] : ''
		));

		$this->Maintainer->create();
		$saved = $this->Maintainer->save($data);

		if (!$saved) {
			$this->out("Error Saving Maintainer");
			$this->out(sprintf("User: %s", json_encode($user)));
			$this->out(sprintf("Data: %s", json_encode($data)));
			$this->out(sprintf("Validation Errors: %s", json_encode($this->Maintainer->validationErrors)));
		}

 		return $this->Maintainer->find('view', $username);
	}

	public function getExisting($maintainer_id, $name) {
		$existing = $this->Maintainer->Package->find('list', array('conditions' => array(
				'Package.maintainer_id' => $maintainer_id,
				'Package.name' => $name
		)));

		if ($existing) {
			$this->out("Package exists! Exiting...");
		}

		return $existing;
	}

}
