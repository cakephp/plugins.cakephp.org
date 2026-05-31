<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\CommandFactoryInterface;
use Cake\Console\ConsoleIo;
use Cake\I18n\Date;
use Cake\Log\Log;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use Packagist\Api\Client;
use Packagist\Api\Result\Package\Version;
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

    private readonly Client $client;

    /**
     * The name of this command.
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
     * @return void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): void
    {
        $packagesTable = $this->fetchTable('Packages');
        $touchedIds = [];

        $io->out('Fetching package list from Packagist...');
        $packages = $this->client->all(['type' => 'cakephp-plugin']);
        $total = count($packages);
        $io->out(sprintf('Found <info>%d</info> packages. Processing...', $total));

        $saved = 0;
        $skipped = 0;
        $failed = 0;
        $i = 0;

        $progress = $io->helper('Progress');
        $progress->init(['total' => $total, 'width' => 60]);
        $io->out('', 0);

        foreach ($packages as $package) {
            $i++;
            $io->out(sprintf('[%d/%d] %s', $i, $total, $package), 1, ConsoleIo::VERBOSE);

            $data = $this->getDataForPackage($package);

            if (
                $data['is_abandoned'] ||
                !$data['latest_stable_version'] ||
                $data['downloads'] < 10 ||
                !$this->hasExplicitCakePhpDependency($data['tag_list'])
            ) {
                $skipped++;
                $progress->increment()->draw();
                continue;
            }

            $entity = $packagesTable->find()->where(['package' => $package])->first();
            if (!$entity) {
                $entity = $packagesTable->newEmptyEntity();
            }

            $entity = $packagesTable->patchEntity($entity, $data);
            if (!$packagesTable->save($entity)) {
                $failed++;
                Log::warning('Unable to save package', [
                    'package' => $package,
                    'errors' => $entity->getErrors(),
                ]);
            } else {
                $saved++;
            }
            $touchedIds[] = $entity->id;
            $progress->increment()->draw();
        }

        $io->out('');
        $io->out(sprintf(
            'Sync complete. Saved: <info>%d</info>, Skipped: <comment>%d</comment>, Failed: <error>%d</error>',
            $saved,
            $skipped,
            $failed,
        ));

        // Remove packages that were not touched
        $io->out('Removing stale packages...');
        $deleted = 0;
        $deleteFailed = 0;
        /** @var \Cake\ORM\ResultSet<array-key, \App\Model\Entity\Package> $toDeletePackages */
        $toDeletePackages = $packagesTable->find()->where(['id NOT IN' => $touchedIds])->all();
        foreach ($toDeletePackages as $package) {
            if (!$packagesTable->delete($package)) {
                $deleteFailed++;
                Log::warning('Unable to delete package', [
                    'package' => $package->package,
                    'id' => $package->id,
                    'errors' => $package->getErrors(),
                ]);
            } else {
                $deleted++;
            }
        }
        $io->out(sprintf(
            'Cleanup complete. Deleted: <info>%d</info>, Failed: <error>%d</error>',
            $deleted,
            $deleteFailed,
        ));
    }

    /**
     * @return array{package: string, description: string, repo_url: string, downloads: int, stars: int, tag_list: array, latest_stable_version: ?string, latest_stable_release_date: ?\Cake\I18n\Date, is_abandoned: bool}
     */
    private function getDataForPackage(string $packageName): array
    {
        /** @var \Packagist\Api\Result\Package $metaDetails */
        $metaDetails = $this->client->get($packageName);

        if ($metaDetails->isAbandoned()) {
            return [
                'package' => $packageName,
                'description' => $metaDetails->getDescription(),
                'repo_url' => $metaDetails->getRepository(),
                'downloads' => $metaDetails->getDownloads()->getTotal(),
                'stars' => $metaDetails->getGithubStars(),
                'tag_list' => [],
                'latest_stable_version' => null,
                'latest_stable_release_date' => null,
                'is_abandoned' => true,
            ];
        }

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

        $stableVersions = array_filter(
            $versions,
            fn(Version $version): int|false => preg_match('/^v?\d+\.\d+(\.\d+)?$/', $version->getVersion()),
        );
        usort($stableVersions, function ($a, $b): int {
            return version_compare($a->getVersion(), $b->getVersion());
        });
        /** @var \Packagist\Api\Result\Package\Version|false $latestStable */
        $latestStable = end($stableVersions);

        return [
            'package' => $packageName,
            'description' => $metaDetails->getDescription(),
            'repo_url' => $metaDetails->getRepository(),
            'downloads' => $metaDetails->getDownloads()->getTotal(),
            'stars' => $metaDetails->getGithubStars(),
            'tag_list' => $meta,
            'latest_stable_version' => $latestStable ? $latestStable->getVersion() : null,
            'latest_stable_release_date' => $this->extractReleaseDate($latestStable ?: null),
            'is_abandoned' => $metaDetails->isAbandoned(),
        ];
    }

    /**
     * @return \Cake\I18n\Date|null
     */
    private function extractReleaseDate(?Version $version): ?Date
    {
        if (!$version instanceof Version || $version->getTime() === '') {
            return null;
        }

        return new Date($version->getTime());
    }

    /**
     * @param list<string> $tags
     * @return bool
     */
    private function hasExplicitCakePhpDependency(array $tags): bool
    {
        foreach ($tags as $tag) {
            if (str_starts_with($tag, 'CakePHP')) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $meta The meta array to adjust
     * @param string $packageConstraint The meta array which contains the current version strings
     * @param string $tagPrefix The prefix which should be used for the tag
     * @param array<array-key, array<int>> $versions The versions to check
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
