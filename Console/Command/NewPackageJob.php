<?php
App::uses('AppShell', 'Console/Command');
App::uses('PackageData', 'Lib');

class NewPackageJob extends AppShell
{
    public $uses = array('Maintainer', 'Github');

    public function work()
    {
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

        $this->out('Package saved');
    }

    public function getMaintainer($username)
    {
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

    public function createMaintainer($username)
    {
        $saved = false;
        $data = $this->Maintainer->retrieveMaintainerData($username);
        if (!empty($data)) {
            $this->Maintainer->create();
            $saved = $this->Maintainer->save(array('Maintainer' => $data));
        }

        if (!$saved) {
            $this->out("Error Saving Maintainer");
            $this->out(sprintf("Data: %s", json_encode($data)));
            $this->out(sprintf("Validation Errors: %s", json_encode($this->Maintainer->validationErrors)));
        }

        return $this->Maintainer->find('view', $username);
    }

    public function getExisting($maintainer_id, $name)
    {
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
