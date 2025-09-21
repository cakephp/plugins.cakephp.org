<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Log\Log;
use Packagist\Api\Client;

/**
 * SyncPackages command.
 */
class SyncPackagesCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'sync_packages';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'sync_packages';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Command description here.';
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $client = new Client();
        $packagesTable = $this->fetchTable('Packages');

        /** @var \Packagist\Api\Result\Result $package */
        foreach ($client->search('', ['type' => 'cakephp-plugin']) as $package) {
            $data = [
                'package' => $package->getName(),
                'description' => $package->getDescription(),
                'repo_url' => $package->getRepository(),
                'packagist_url' => $package->getUrl(),
                'downloads' => $package->getDownloads(),
                'stars' => $package->getFavers()
            ];

            $entity = $packagesTable->find()->where(['package' =>  $package->getName()])->first();
            if (!$entity) {
                $entity = $packagesTable->newEmptyEntity();
            }

            $entity = $packagesTable->patchEntity($entity, $data);
            if (!$packagesTable->save($entity)) {
                Log::warning('Unable to save package', [
                    'package' => $package->getName(),
                    'errors' => $entity->getErrors()
                ]);
            }

        }
    }
}
