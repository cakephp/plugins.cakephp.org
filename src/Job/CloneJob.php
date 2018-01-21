<?php
namespace App\Job;

use App\Model\Entity\Package;
use App\Traits\LogTrait;
use Cake\Datasource\ModelAwareTrait;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use Cake\Http\Client;
use josegonzalez\Queuesadilla\Job\Base;
use RuntimeException;

class CloneJob
{
    use LogTrait;

    use ModelAwareTrait;

    public function perform(Base $job)
    {
        $packageId = $job->data('package_id');
        if (empty($packageId)) {
            $this->error('No package id specified');

            return false;
        }

        $this->loadModel('Packages');
        $package = $this->Packages->find()
                                  ->contain(['Maintainers'])
                                  ->where(['Packages.id' => $packageId])
                                  ->first();

        if (empty($package)) {
            $this->error(sprintf('No package found in database for %d', $packageId));

            return false;
        }

        $this->info(sprintf('Cloning to: %s', $package->cloneDir()));
        $cloned = $this->ensurePackageExists($package);
        if (!$cloned) {
            $this->error('Not able to clone the package');

            return false;
        }

        return true;
        $package->deleted = !!!$cloned;
        $this->info(sprintf(
            '%s: %s [deleted: %s]',
            $package->id,
            $package->cloneUrl(),
            $package->deleted ? 'true' : 'false'
        ), ['deleted' => $package->deleted ? 'true' : 'false']);
        $this->Packages->save($package);

        return true;
    }

    /**
     * Executes an external shell command and pipes its output to the stdout
     *
     * @param string $command the command to execute
     * @return void
     * @throws \RuntimeException if any errors occurred during the execution
     */
    protected function callProcess($command)
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $this->debug('Running ' . $command);
        $process = proc_open(
            $command,
            $descriptorSpec,
            $pipes
        );
        if (!is_resource($process)) {
            $this->error('Could not start subprocess.');

            return false;
        }
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exit = proc_close($process);

        if ($exit !== 0) {
            throw new RuntimeException($error);
        }

        if (!empty($output)) {
            $this->info(trim($output));
        }
    }

    protected function callProcessInDirectory($command, $path)
    {
        try {
            $cwd = getcwd();

            // Windows makes running multiple commands at once hard.
            chdir($path);
            $this->callProcess($command);
            chdir($cwd);
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
            $this->error(sprintf('Could not run `%s`: %s', $command, $error));

            return false;
        }

        return true;
    }

    protected function ensurePackageExists(Package $package)
    {
        if ($package->isCloned()) {
            return true;
        }

        $this->info(sprintf('Retrieving zip location: %s', $package->zipballUrl()));
        $client = new Client;
        $r = $client->get($package->zipballUrl());
        if ($r->statusCode() != 302) {
            $this->error(sprintf('Error code', $r->statusCode()));

            return false;
        }

        $url = $r->getHeader('location')[0];
        $this->info(sprintf('Retrieving zip: %s', $url));
        $response = $client->get($url);
        if ($response->statusCode() != 200) {
            $this->error(sprintf('Error code', $response->statusCode()));

            return false;
        }

        $this->info(sprintf('Writing zip: %s', $package->zipballPath()));
        $file = new File($package->zipballPath(), true, 0644);
        if (!$file->write($response->body())) {
            $this->error('Unable to extract file');

            return false;
        }

        $folder = new Folder($package->cloneBasePath(), true);
        $errors = $folder->errors();
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return false;
        }

        $this->info(sprintf('Extracting zip: %s => %s', $package->zipballPath(), $package->cloneDir()));
        $command = sprintf('unzip %s -d %s', $package->zipballPath(), $package->cloneDir());
        $path = TMP;
        return $this->callProcessInDirectory($command, $path);
    }
}
