<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Log\Log;
use Composer\Semver\Semver;
use Packagist\Api\Client;

/**
 * SyncPackages command.
 */
class SyncPackagesCommand extends Command
{
    private Client $client;

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
     * @param \Cake\Console\CommandFactoryInterface|null $factory
     */
    public function __construct(
        ?CommandFactoryInterface $factory = null,
    ) {
        parent::__construct($factory);
        $this->client = new Client();
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
        $packagesTable = $this->fetchTable('Packages');

        /** @var \Packagist\Api\Result\Result $package */
        foreach ($this->client->search('', ['type' => 'cakephp-plugin']) as $package) {
            $tags = $this->getTagsForPackage($package->getName());
            $data = [
                'package' => $package->getName(),
                'description' => $package->getDescription(),
                'repo_url' => $package->getRepository(),
                'packagist_url' => $package->getUrl(),
                'downloads' => $package->getDownloads(),
                'stars' => $package->getFavers(),
                'tag_list' => $tags,
            ];

            $entity = $packagesTable->find()->where(['package' => $package->getName()])->first();
            if (!$entity) {
                $entity = $packagesTable->newEmptyEntity();
            }

            $entity = $packagesTable->patchEntity($entity, $data);
            if (!$packagesTable->save($entity)) {
                Log::warning('Unable to save package', [
                    'package' => $package->getName(),
                    'errors' => $entity->getErrors(),
                ]);
            }
        }
    }

    /**
     * @param string $packageName
     * @return array
     */
    private function getTagsForPackage(string $packageName): array
    {
        $metaDetails = $this->client->getComposer($packageName);

        /** @var \Packagist\Api\Result\Package $metaDetails */
        $metaDetails = $metaDetails[$packageName];
        $versions = $metaDetails->getVersions();

        $meta = [];
        // Check each version
        foreach ($versions as $versionMeta) {
            $phpRequire = $versionMeta->getRequire()['php'] ?? null;
            if ($phpRequire) {
                $meta = $this->checkPHPVersion($meta, $phpRequire);
            }

            $cakephpRequire = $versionMeta->getRequire()['cakephp/cakephp'] ?? null;
            if ($cakephpRequire) {
                $meta = $this->checkCakeVersion($meta, $cakephpRequire);
            } else {
                // Cake has standalone sub-packages
                $subpackages = [
                    'cakephp/cache',
                    'cakephp/collection',
                    'cakephp/console',
                    'cakephp/database',
                    'cakephp/datasource',
                    'cakephp/event',
                    'cakephp/form',
                    'cakephp/http',
                    'cakephp/i18n',
                    'cakephp/log',
                    'cakephp/orm',
                    'cakephp/utility',
                    'cakephp/validation',
                ];

                foreach ($subpackages as $subpackage) {
                    $cakephpRequire = $versionMeta->getRequire()[$subpackage] ?? null;
                    if ($cakephpRequire) {
                        $meta = $this->checkCakeVersion($meta, $cakephpRequire);
                    }
                }
            }
        }

        return $meta;
    }

    /**
     * @param array $meta
     * @param string $packageConstraint
     * @return array
     */
    private function checkPHPVersion(array $meta, string $packageConstraint): array
    {
        $phpVersions = [
            '8.4.0' => 'PHP: 8.4',
            '8.3.0' => 'PHP: 8.3',
            '8.2.0' => 'PHP: 8.2',
            '8.1.0' => 'PHP: 8.1',
            '8.0.0' => 'PHP: 8.0',
            '7.4.0' => 'PHP: 7.4',
            '7.3.0' => 'PHP: 7.3',
            '7.2.0' => 'PHP: 7.2',
            '7.1.0' => 'PHP: 7.1',
            '7.0.0' => 'PHP: 7.0',
        ];

        foreach ($phpVersions as $version => $label) {
            if (Semver::satisfies($version, $packageConstraint) && !in_array($label, $meta)) {
                $meta[] = $label;
            }
        }

        return $meta;
    }

    /**
     * @param array $meta
     * @param string $packageConstraint
     * @return array
     */
    private function checkCakeVersion(array $meta, string $packageConstraint): array
    {
        $cakeVersions = [
            '5.0.0' => 'CakePHP: 5.0',
            '4.0.0' => 'CakePHP: 4.0',
            '3.0.0' => 'CakePHP: 3.0',
        ];

        foreach ($cakeVersions as $version => $label) {
            if (Semver::satisfies($version, $packageConstraint) && !in_array($label, $meta)) {
                $meta[] = $label;
            }
        }

        return $meta;
    }
}
