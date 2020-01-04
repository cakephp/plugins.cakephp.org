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

    public function __construct()
    {
        $this->loadModel('Packages');
    }

    public function perform(Base $job)
    {
        $packageId = $job->data('package_id');
        if (empty($packageId)) {
            $this->error('No package id specified');

            return false;
        }

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

        $this->info(sprintf('Package cloned: %s', $package->cloneDir()));

        $package->deleted = !$cloned;
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
            2 => ['pipe', 'w'],
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

        $this->info(sprintf('Retrieving zip location: %s', $package->cloneZipballUrl()));
        $client = new Client();
        $response = $client->get($package->cloneZipballUrl());
        if ($response->getStatusCode() != 302) {
            $this->error(sprintf('Error code', $response->statusCode()));

            return false;
        }

        $url = $response->getHeader('location')[0];
        $this->info(sprintf('Retrieving zip: %s', $url));
        $response = $client->get($url);
        if ($response->getStatusCode() != 200) {
            $this->error(sprintf('Error code', $response->statusCode()));

            return false;
        }

        $this->info(sprintf('Writing zip: %s', $package->cloneZipballPath()));
        $file = new File($package->cloneZipballPath(), true, 0644);
        if (!$file->write($response->getStringBody())) {
            $this->error('Unable to extract file');

            return false;
        }

        $paths = [
            $package->cloneBasePath(),
            $package->cloneMaintainerPath(),
        ];
        foreach ($paths as $path) {
            $this->info(sprintf('Creating path: %s', $path));
            $folder = new Folder($path, true);
            $errors = $folder->errors();
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->error($error);
                }

                return false;
            }
        }

        $tmpPath = TMP . uniqid('repo');

        $this->info(sprintf('Extracting zip: %s => %s', $package->cloneZipballPath(), $tmpPath));
        $command = sprintf('unzip %s -d %s', $package->cloneZipballPath(), $tmpPath);
        $path = TMP;
        if (!$this->callProcessInDirectory($command, $path)) {
            $this->error('Unzip failed');

            return false;
        }

        $folder = new Folder($tmpPath, false);
        $contents = $folder->read();
        $extracttFolder = $contents[0][0];

        $this->info('Moving contents into place');
        $command = sprintf('mv %s/%s %s', $tmpPath, $extracttFolder, $package->cloneDir());
        $path = TMP;
        if (!$this->callProcessInDirectory($command, $path)) {
            $this->error('Move failed');

            return false;
        }

        return true;
    }
}
