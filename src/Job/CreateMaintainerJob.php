<?php
namespace App\Job;

use App\Model\Entity\Package;
use App\Traits\LogTrait;
use Cake\Datasource\ModelAwareTrait;
use Cake\Http\Client;
use Cake\Utility\Hash;
use josegonzalez\Queuesadilla\Job\Base;
use RuntimeException;

class CreateMaintainerJob
{
    use LogTrait;

    use ModelAwareTrait;

    public function __construct()
    {
        $this->loadModel('Maintainers');
        $this->Maintainers->getConnection()->getDriver()->enableAutoQuoting();
    }

    public function perform(Base $job)
    {
        $username = $job->data('username');
        $maintainer = $this->Maintainers->find()
                                        ->where(['username' => $username])
                                        ->first();
        if (!empty($maintainer)) {
            $this->info('Maintainer already exists');

            return true;
        }

        try {
            $this->createMaintainer($username);
        } catch (Exception $e) {
            $this->error($e->getMessage());

            return false;
        }
    }

    public function createMaintainer($username)
    {
        $data = $this->retrieveMaintainerData($username);
        if (empty($data)) {
            $this->error('Missing maintainer data');

            return false;
        }

        if (!empty($data['email'])) {
            $maintainer = $this->Maintainers->findByEmail($data['email'])->first();
            if (!empty($maintainer)) {
                $this->info(sprintf('Updating maintainer %d', $maintainer->id));
                $maintainer->set($data);

                if (!$this->Maintainers->save($maintainer)) {
                    $this->error(sprintf("Data: %s", json_encode($data)));
                    $this->error(sprintf("Validation Errors: %s", json_encode($maintainer->getErrors())));

                    return false;
                }

                return true;
            }
        }

        $maintainer = $this->Maintainers->newEntity($data);
        if (!$this->Maintainers->save($maintainer)) {
            $this->error(sprintf("Data: %s", json_encode($data)));
            $this->error(sprintf("Validation Errors: %s", json_encode($maintainer->getErrors())));

            return false;
        }
    }

    /**
     * Gets github user information
     *
     * @param string $username Name of a maintainer
     * @return mixed array of results or false if none found
     */
    public function retrieveMaintainerData($username)
    {
        $client = new Client([
            'timeout' => 2,
        ]);
        $response = $client->get(sprintf('https://api.github.com/users/%s', $username));

        if ($response->getStatusCode() != 200) {
            return [];
        }

        if ($response->getJson() == null) {
            return [];
        }

        $user = $response->getJson();
        $data = [
            'github_id' => (int)Hash::get($user, 'id', ''),
            'username' => Hash::get($user, 'login', ''),
            'gravatar_id' => Hash::get($user, 'gravatar_id', ''),
            'avatar_url' => Hash::get($user, 'avatar_url', ''),
            'name' => Hash::get($user, 'name', ''),
            'company' => Hash::get($user, 'company', ''),
            'name' => Hash::get($user, 'name', ''),
            'url' => Hash::get($user, 'blog', ''),
            'email' => Hash::get($user, 'email', ''),
            'location' => Hash::get($user, 'location', ''),
        ];
        foreach (array_keys($data) as $key) {
            if (empty($data[$key])) {
                unset($data[$key]);
            }
        }

        $data['group'] = 'maintainer';
        $data['activation_key'] = '';

        return $data;
    }
}
