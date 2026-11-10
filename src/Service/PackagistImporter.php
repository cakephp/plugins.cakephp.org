<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\PackagesTable;
use Cake\Cache\Cache;
use Cake\I18n\Date;
use Cake\Log\Log;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use Packagist\Api\Client as PackagistClient;
use Packagist\Api\Result\Package\Version;
use UnexpectedValueException;

/**
 * Imports eligible Packagist plugins and their compatibility metadata.
 */
class PackagistImporter
{
    private const array CAKEPHP_SUBPACKAGES = [
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

    private const array PHP_VERSIONS = [
        '7' => [0, 1, 2, 3, 4],
        '8' => [0, 1, 2, 3, 4, 5],
    ];

    private const array CAKEPHP_VERSIONS = [
        '3' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        '4' => [0, 1, 2, 3, 4, 5, 6],
        '5' => [0, 1, 2, 3, 4],
    ];

    /**
     * Resolve the API client and repository through the application container.
     */
    public function __construct(
        private readonly PackagesTable $packages,
        private readonly PackagistClient $client,
    ) {
    }

    /**
     * @param callable(string, int, int): void|null $progress Optional package progress callback.
     * @return array{total: int, saved: int, skipped: int, failed: int, deleted: int, deleteFailed: int}
     */
    public function sync(?callable $progress = null): array
    {
        /** @var list<string> $names */
        $names = $this->client->all(['type' => 'cakephp-plugin']);
        if ($names === []) {
            throw new UnexpectedValueException('Packagist returned no plugins; existing packages were retained.');
        }
        $result = ['total' => count($names), 'saved' => 0, 'skipped' => 0, 'failed' => 0, 'deleted' => 0, 'deleteFailed' => 0];
        $touchedIds = [];
        foreach ($names as $index => $name) {
            $data = $this->getDataForPackage($name);
            if ($data['is_abandoned'] || !$data['latest_stable_version'] || $data['downloads'] < 10 || !$this->hasExplicitCakePhpDependency($data['tag_list'])) {
                $result['skipped']++;
            } else {
                $entity = $this->packages->find()->where(['package' => $name])->first() ?? $this->packages->newEmptyEntity();
                $entity = $this->packages->patchEntity($entity, $data);
                if ($this->packages->save($entity)) {
                    $result['saved']++;
                } else {
                    $result['failed']++;
                    Log::warning('Unable to save package', ['package' => $name, 'errors' => $entity->getErrors()]);
                }
                if ($entity->id !== null) {
                    $touchedIds[] = $entity->id;
                }
            }
            if ($progress !== null) {
                $progress($name, $index + 1, $result['total']);
            }
        }
        $stale = $this->packages->find();
        if ($touchedIds !== []) {
            $stale->where(['id NOT IN' => $touchedIds]);
        }
        foreach ($stale->all() as $package) {
            if ($this->packages->delete($package)) {
                $result['deleted']++;
            } else {
                $result['deleteFailed']++;
                Log::warning('Unable to delete package', ['package' => $package->package, 'errors' => $package->getErrors()]);
            }
        }
        Cache::delete('package_filter_tags_v1');

        return $result;
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
     * Extract the release date
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
        return array_any($tags, fn(string $tag): bool => str_starts_with($tag, 'CakePHP'));
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
