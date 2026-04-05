<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\Log\Log;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use Packagist\Api\Client;
use UnexpectedValueException;

/**
 * SyncPackages command.
 */
class SyncPackagesCommand extends Command
{
    private const CAKEPHP_SUBPACKAGES = [
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

    private const PHP_VERSIONS = [
        '7' => [0, 1, 2, 3, 4],
        '8' => [0, 1, 2, 3, 4, 5],
    ];

    private const CAKEPHP_VERSIONS = [
        '3' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        '4' => [0, 1, 2, 3, 4, 5, 6],
        '5' => [0, 1, 2, 3],
    ];

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
        $touchedIds = [];

        $data = $this->client->all(['type' => 'cakephp-plugin']);
        foreach ($data as $package) {
            $data = $this->getDataForPackage($package);

            if ($data['is_abandoned'] || $data['downloads'] < 50) {
                continue;
            }

            $entity = $packagesTable->find()->where(['package' => $package])->first();
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
            $touchedIds[] = $entity->id;
        }

        // Remove packages that were not touched
        $toDeletePackages = $packagesTable->find()->where(['id NOT IN' => $touchedIds])->all();
        foreach ($toDeletePackages as $package) {
            if (!$packagesTable->delete($package)) {
                Log::warning('Unable to delete package', [
                    'package' => $package->package,
                    'id' => $package->id,
                    'errors' => $package->getErrors(),
                ]);
            }
        }
    }

    /**
     * @param string $packageName
     * @return array
     */
    private function getDataForPackage(string $packageName): array
    {
        /** @var \Packagist\Api\Result\Package $metaDetails */
        $metaDetails = $this->client->get($packageName);
        $versions = $metaDetails->getVersions();

        $meta = [];
        // Check each version
        foreach ($versions as $versionMeta) {
            $phpRequire = $versionMeta->getRequire()['php'] ?? null;
            if ($phpRequire) {
                $meta = $this->appendVersionTags($meta, $phpRequire, 'PHP', self::PHP_VERSIONS);
            }

            $cakephpRequire = $versionMeta->getRequire()['cakephp/cakephp'] ?? null;
            if ($cakephpRequire) {
                $meta = $this->appendVersionTags($meta, $cakephpRequire, 'CakePHP', self::CAKEPHP_VERSIONS);
            } else {
                // Cake has standalone sub-packages
                foreach (self::CAKEPHP_SUBPACKAGES as $subpackage) {
                    $cakephpRequire = $versionMeta->getRequire()[$subpackage] ?? null;
                    if ($cakephpRequire) {
                        $meta = $this->appendVersionTags($meta, $cakephpRequire, 'CakePHP', self::CAKEPHP_VERSIONS);
                    }
                }
            }
        }

        $stableVersions = array_filter($versions, fn($v) => preg_match('/^v?\d+\.\d+\.\d+$/', $v->getVersion()));
        usort($stableVersions, function ($a, $b) {
            return version_compare($a->getVersion(), $b->getVersion());
        });
        $latestStable = end($stableVersions);

        return [
            'package' => $packageName,
            'description' => $metaDetails->getDescription(),
            'repo_url' => $metaDetails->getRepository(),
            'downloads' => $metaDetails->getDownloads()->getTotal(),
            'stars' => $metaDetails->getGithubStars(),
            'tag_list' => $meta,
            'latest_stable_version' => $latestStable ? $latestStable->getVersion() : null,
            'is_abandoned' => $metaDetails->isAbandoned(),
        ];
    }

    /**
     * @param array $meta The meta array to adjust
     * @param string $packageConstraint The meta array which contains the current version strings
     * @param string $tagPrefix The prefix which should be used for the tag
     * @param array<string, array<int>> $versions The versions to check
     * @return array
     */
    private function appendVersionTags(
        array $meta,
        string $packageConstraint,
        string $tagPrefix,
        array $versions,
    ): array {
        foreach ($versions as $majorVersionNr => $minorVersions) {
            foreach ($minorVersions as $minorVersionNr) {
                $minorVersion = sprintf('%s.%s', $majorVersionNr, $minorVersionNr);
                $tagLabel = sprintf('%s: %s', $tagPrefix, $minorVersion);
                if (
                    $this->constraintsIntersect($packageConstraint, $minorVersion) &&
                    !in_array($tagLabel, $meta, true)
                ) {
                    $meta[] = $tagLabel;
                }
            }
        }

        return $meta;
    }

    /**
     * @param string $leftConstraint
     * @param string $rightConstraint
     * @return bool
     */
    private function constraintsIntersect(string $leftConstraint, string $rightConstraint): bool
    {
        $versionParser = new VersionParser();

        try {
            return Intervals::haveIntersections(
                $versionParser->parseConstraints($leftConstraint),
                $versionParser->parseConstraints($rightConstraint),
            );
        } catch (UnexpectedValueException) {
            return false;
        } finally {
            Intervals::clear();
        }
    }
}
