<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\PackagesTable;
use App\Utility\CakephpVersion;
use App\Utility\ComposerKeyword;
use Cake\Cache\Cache;
use Cake\I18n\Date;
use Cake\Log\Log;
use Composer\Semver\Intervals;
use Composer\Semver\VersionParser;
use Packagist\Api\Client as PackagistClient;
use Packagist\Api\Result\Package\Version;
use UnexpectedValueException;
use function Cake\Database\Expression\notIn;

/**
 * Imports eligible Packagist plugins and their compatibility metadata.
 *
 * @phpstan-import-type DevelopmentRelease from \App\Model\Entity\Package
 */
class PackagistImporter
{
    private const array CAKEPHP_SUBPACKAGES = [
        'cakephp/cache',
        'cakephp/collection',
        'cakephp/console',
        'cakephp/container',
        'cakephp/core',
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
            $eligibleRelease = $data['latest_stable_version'] !== null && $data['downloads'] >= 10;
            if ($data['is_abandoned'] || (!$eligibleRelease && $data['development_compatibility'] === []) || !$this->hasExplicitCakePhpDependency($data['tag_list'])) {
                $result['skipped']++;
            } else {
                $entity = $this->packages->find()->where(['package' => $name])->first() ?? $this->packages->newEmptyEntity();
                $keywords = $this->packages->Keywords->forNames($data['keyword_names']);
                unset($data['keyword_names']);
                $entity = $this->packages->patchEntity($entity, $data);
                $entity->set('keywords', $keywords);
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
            $stale->where(['id' => notIn($touchedIds)]);
        }
        foreach ($stale->all() as $package) {
            if ($this->packages->delete($package)) {
                $result['deleted']++;
            } else {
                $result['deleteFailed']++;
                Log::warning('Unable to delete package', ['package' => $package->package, 'errors' => $package->getErrors()]);
            }
        }
        Cache::clear('catalogue');

        return $result;
    }

    /**
     * @return array{package: string, description: string, repo_url: string, downloads: int, monthly_downloads: int, stars: int, tag_list: list<string>, keyword_names: list<string>, latest_stable_version: ?string, latest_stable_release_date: ?\Cake\I18n\Date, is_abandoned: bool, development_compatibility: array<int, DevelopmentRelease>}
     */
    private function getDataForPackage(string $packageName): array
    {
        /** @var \Packagist\Api\Result\Package $metaDetails */
        $metaDetails = $this->client->get($packageName);
        $downloads = $metaDetails->getDownloads();
        if ($downloads === null) {
            throw new UnexpectedValueException(sprintf('Packagist returned no download metadata for %s; existing packages were retained.', $packageName));
        }

        if ($metaDetails->isAbandoned()) {
            return [
                'package' => $packageName,
                'description' => $metaDetails->getDescription(),
                'repo_url' => $metaDetails->getRepository(),
                'downloads' => $downloads->getTotal(),
                'monthly_downloads' => $downloads->getMonthly(),
                'stars' => $metaDetails->getGithubStars(),
                'tag_list' => [],
                'keyword_names' => [],
                'latest_stable_version' => null,
                'latest_stable_release_date' => null,
                'is_abandoned' => true,
                'development_compatibility' => [],
            ];
        }

        $versions = $metaDetails->getVersions();
        $development = $this->developmentCompatibility($versions);

        $meta = [];
        // Check each version
        foreach ($versions as $versionMeta) {
            $phpRequire = $versionMeta->getRequire()['php'] ?? null;
            if ($phpRequire) {
                $meta = $this->appendVersionTags($meta, $phpRequire, 'PHP', self::PHP_VERSIONS);
            }

            $cakephpRequire = $versionMeta->getRequire()['cakephp/cakephp'] ?? null;
            if ($cakephpRequire) {
                $meta = $this->appendVersionTags($meta, $cakephpRequire, 'CakePHP', CakephpVersion::stableVersions());
            } else {
                // Cake has standalone sub-packages
                foreach (self::CAKEPHP_SUBPACKAGES as $subpackage) {
                    $cakephpRequire = $versionMeta->getRequire()[$subpackage] ?? null;
                    if ($cakephpRequire) {
                        $meta = $this->appendVersionTags($meta, $cakephpRequire, 'CakePHP', CakephpVersion::stableVersions());
                    }
                }
            }
        }

        foreach (array_keys($development) as $major) {
            $meta[] = 'CakePHP: ' . CakephpVersion::developmentLabel($major);
        }

        $stableVersions = array_filter(
            $versions,
            static fn(Version $version): bool => preg_match('/^v?\d+\.\d+(\.\d+)?$/', $version->getVersion()) === 1,
        );
        usort($stableVersions, function ($a, $b): int {
            return version_compare($a->getVersion(), $b->getVersion());
        });
        /** @var \Packagist\Api\Result\Package\Version|false $latestStable */
        $latestStable = end($stableVersions);
        $keywordRelease = $latestStable ?: null;
        if ($keywordRelease === null && $development !== []) {
            $installVersion = array_first($development)['version'];
            foreach ($versions as $version) {
                if ($version->getVersion() === $installVersion) {
                    $keywordRelease = $version;
                    break;
                }
            }
        }
        $keywords = [];
        foreach ($keywordRelease?->getKeywords() ?? [] as $keyword) {
            if (is_string($keyword)) {
                $keyword = ComposerKeyword::normalize($keyword);
                if (ComposerKeyword::isValid($keyword)) {
                    $keywords[] = $keyword;
                }
            }
        }

        return [
            'package' => $packageName,
            'description' => $metaDetails->getDescription(),
            'repo_url' => $metaDetails->getRepository(),
            'downloads' => $downloads->getTotal(),
            'monthly_downloads' => $downloads->getMonthly(),
            'stars' => $metaDetails->getGithubStars(),
            'tag_list' => $meta,
            'keyword_names' => array_values(array_unique($keywords)),
            'latest_stable_version' => $latestStable ? $latestStable->getVersion() : null,
            'latest_stable_release_date' => $this->extractReleaseDate($latestStable ?: null),
            'is_abandoned' => $metaDetails->isAbandoned(),
            'development_compatibility' => $development,
        ];
    }

    /**
     * Keep an installable source release for each upcoming major, preferring stable tags.
     *
     * @param array<\Packagist\Api\Result\Package\Version> $versions Packagist releases and branches.
     * @return array<int, DevelopmentRelease>
     */
    private function developmentCompatibility(array $versions): array
    {
        usort($versions, static function (Version $left, Version $right): int {
            $leftStable = VersionParser::parseStability($left->getVersion()) === 'stable';
            $rightStable = VersionParser::parseStability($right->getVersion()) === 'stable';
            if ($leftStable !== $rightStable) {
                return $rightStable <=> $leftStable;
            }

            return $leftStable
                ? version_compare($right->getVersion(), $left->getVersion())
                : [$right->getTime(), $right->getVersion()] <=> [$left->getTime(), $left->getVersion()];
        });
        $matches = [];
        foreach ($versions as $version) {
            $requirements = $version->getRequire();
            $constraints = array_intersect_key($requirements, array_flip(['cakephp/cakephp', ...self::CAKEPHP_SUBPACKAGES]));
            foreach (CakephpVersion::developmentMajors() as $major) {
                if (isset($matches[$major]) || $constraints === []) {
                    continue;
                }
                // Unbounded dependencies such as * and >=4 do not explicitly target an upcoming major.
                $supported = true;
                foreach ($constraints as $constraint) {
                    try {
                        $parsed = new VersionParser()->parseConstraints($this->dependencyConstraint($constraint));
                        if ($parsed->getUpperBound()->isPositiveInfinity() || !$this->constraintsIntersect($constraint, '^' . $major . '.0')) {
                            $supported = false;
                            break;
                        }
                    } catch (UnexpectedValueException) {
                        $supported = false;
                        break;
                    }
                }
                if ($supported) {
                    $matches[$major] = [
                        'version' => $version->getVersion(),
                        'stability' => VersionParser::parseStability($version->getVersion()),
                        'cakephp_constraint' => implode(' & ', array_unique(array_values($constraints))),
                        'php_constraint' => $requirements['php'] ?? null,
                    ];
                }
            }
        }

        return $matches;
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
     * @param list<string> $meta The meta array to adjust
     * @param string $packageConstraint The meta array which contains the current version strings
     * @param string $tagPrefix The prefix which should be used for the tag
     * @param array<array-key, array<int>> $versions The versions to check
     * @return list<string>
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
                $versionParser->parseConstraints($this->dependencyConstraint($leftConstraint)),
                $versionParser->parseConstraints($this->dependencyConstraint($rightConstraint)),
            );
        } catch (UnexpectedValueException) {
            return false;
        } finally {
            Intervals::clear();
        }
    }

    /**
     * Consumers of a published package resolve the target of an inline alias.
     */
    private function dependencyConstraint(string $constraint): string
    {
        return preg_replace('/([^,\s]+)\s+as\s+([^,\s]+)/', '$2', $constraint) ?? $constraint;
    }
}
